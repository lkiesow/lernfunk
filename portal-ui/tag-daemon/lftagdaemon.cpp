/* 
	Copyright (c) 2006 - 2010  Universitaet Osnabrueck, virtUOS 
	Authors: Lars Kiesow

	This file is part of Lernfunk. 

	Lernfunk is free software: you can redistribute it and/or modify 
	it under the terms of the GNU General Public License as published by 
	the Free Software Foundation, either version 3 of the License, or 
	(at your option) any later version. 

	Lernfunk is distributed in the hope that it will be useful, 
	but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the 
	GNU General Public License for more details. 

	You should have received a copy of the GNU General Public License 
	along with Lernfunk.  If not, see <http://www.gnu.org/licenses/>. 
*/

#include <algorithm>
#include <cerrno>
#include <cstdio>
#include <cstdlib>
#include <cstring>
#include <ctime>
#include <fcntl.h>
#include <fstream>
#include <iostream>
#include <map>
#include <mysql.h>
#include <set>
#include <string>
#include <syslog.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <unistd.h>
using namespace std;

#define null 0

#if defined(__WIN32__) || defined(_WIN32) || defined(WIN32) || defined(__WINDOWS__) || defined(__TOS_WIN__)
	#include <windows.h>
	inline void delay( unsigned long ms ) {
		Sleep( ms );
	}
#else  /* presume POSIX */
	#include <unistd.h>
	inline void delay( unsigned long ms ) {
		usleep( ms * 1000 );
	}
#endif

typedef unsigned int uint;
typedef set<string> strset;
typedef set<int> intset;
typedef map<string, uint> tagmap;
typedef struct {
	struct {
		string server;
		string user;
		string password;
		string database;
	} mysql;
	struct {
		uint minlength;
		uint minquantity;
	} tag;
	struct {
		intset wdays;
		intset hours;
	} timer;
	strset filter;
	struct {
		string success;
		string failure;
	} log;
} config;


string trim(const string & str) {

	if (str.size() == 0)
		return "";

	uint i(0);
	for (; i < str.size() && str[i] == ' '; i++) { }
	uint j(str.size() - 1);
	for (; j >= i && str[j] == ' '; j--) { }
	return str.substr(i, j-i+1);

}


void insert_tag(const config & cfg, tagmap & tags, const string & str, const uint & pos, const uint & len) {

	string substr = str.substr(pos, len);
	if (cfg.tag.minlength <= len && cfg.filter.find(substr) == cfg.filter.end()) {
		if (tags.find(substr) == tags.end()) {
			tags[substr] = 1;
		} else {
			tags[substr]++;
		}
	}

}


config & set_default_config(config & cfg) {
	cfg.mysql.server    = "localhost";
	cfg.mysql.user      = "root";
	cfg.mysql.password  = "";
	cfg.mysql.database  = "lernfunk";
	cfg.tag.minlength   = 1;
	cfg.tag.minquantity = 1;
	cfg.log.success     = "";
	cfg.log.failure     = "";
	return cfg;
}


void read_config(config & cfg, const string & filename) {

	ifstream in(filename.c_str());
	string cat("");
	uint line(0);
	while (in.good()) {
		string str;
		getline(in, str);
		line++;
		str = trim(str);
		if (str == "" || str[0] == '#') {
			// ignore empty rows and comments
		} else if (str.size() > 2 && str[0] == '[' && str[str.size() - 1] == ']') {
			// is category
			cat = str.substr(1, str.size() - 2);
			if (cat != "mysql" && cat != "tag" && cat != "filter" && cat != "timer" && cat != "log") {
				cerr << "ERROR: unknown category identifier '" << cat 
					<< "' in '" << filename << "' on line " << line << endl;
				exit(EXIT_FAILURE);
			}
		} else {
			// configuration
			size_t pos( str.find('=') );
			if (pos == string::npos) {
				cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
				exit(EXIT_FAILURE);
			}
			string name( trim(str.substr(0, pos)) );
			string val( trim(str.substr(pos + 1, str.size() - pos - 1)) );
			if (cat == "mysql") {
				if (name == "server")
					cfg.mysql.server = val;
				else if (name == "user")
					cfg.mysql.user = val;
				else if (name == "password")
					cfg.mysql.password = val;
				else if (name == "database")
					cfg.mysql.database = val;
				else {
					cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
					exit(EXIT_FAILURE);
				}
			} else if (cat == "tag") {
				if (name == "minlength")
					cfg.tag.minlength = atoi(val.c_str());
				else if (name == "minquantity")
					cfg.tag.minquantity = atoi(val.c_str());
				else {
					cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
					exit(EXIT_FAILURE);
				}
			} else if (cat == "filter") {
				if (name == "exclude")
					cfg.filter.insert(val);
				else {
					cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
					exit(EXIT_FAILURE);
				}
			} else if (cat == "timer") {
				if (name == "wday")
					cfg.timer.wdays.insert(atoi(val.c_str()));
				else if (name == "hour")
					cfg.timer.hours.insert(atoi(val.c_str()));
				else {
					cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
					exit(EXIT_FAILURE);
				}
			} else if (cat == "log") {
				if (name == "success")
					cfg.log.success = val;
				else if (name == "failure")
					cfg.log.failure = val;
				else {
					cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
					exit(EXIT_FAILURE);
				}
			} else {
					cerr << "ERROR: syntax error in configuration file '" << filename << "' on line " << line << endl;
					exit(EXIT_FAILURE);
				}
		}

	}
	in.close();

}


void get_tags(const config & cfg, const string & str, tagmap & tags) {

	unsigned short task = 0;
	uint start(0);
	for (uint i(0); i < str.size(); i++) {
		if (task == 0) {
			// find ppercase
			if (str[i] >= 'A' && str[i] <= 'Z') {
				start = i;
				task  = 1;
			} 
		} else if (task == 1) {
				// find end of word
				if ( ( (str[i] < 'A') && (str[i] >= 0) )  || ( (str[i] > 'Z') && (str[i] < 'a') ) ) {
					task = 0;
					insert_tag(cfg, tags, str, start, i - start);
				}
		}
	}
	if (task == 1)
		insert_tag(cfg, tags, str, start, str.size() - start);

}


string timestr() {
	time_t rawtime;
	time ( &rawtime );
	string now(ctime( &rawtime ));
	now.resize(now.length() - 1); // remove \n
	return now;
}


void log_error(const config & cfg, string msg) {

	if (cfg.log.failure.length()) {
		ofstream err(cfg.log.failure.c_str(), ios::out | ios::app);
		err << "err: " << timestr() << ": " << msg << endl;
		err.close();
	}

}


void log_success(const config & cfg, string msg) {

	if (cfg.log.success.length()) {
		ofstream out(cfg.log.success.c_str(), ios::out | ios::app);
		out << "msg: " << timestr() << ": " << msg << endl;
		out.close();
	}

}


void generate_tags(const config & cfg) {

    MYSQL * conn = mysql_init(NULL);

    /* Connect to database */
    if (!mysql_real_connect(conn, cfg.mysql.server.c_str(),
                cfg.mysql.user.c_str(), cfg.mysql.password.c_str(),
								cfg.mysql.database.c_str(), 0, NULL, 0)) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }

    /* send SQL query for mediaobjects */
    if (mysql_query(conn, "SELECT object_id, title, description FROM mediaobject;")) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }

		tagmap tags;
    MYSQL_ROW row;
    MYSQL_RES * res( mysql_use_result(conn) );

    /* output fields 1 and 2 of each row */
    while ((row = mysql_fetch_row(res)) != NULL) {
			get_tags(cfg, row[1] + string(" ") + row[2], tags);
		}

		/* The same for series */
    if (mysql_query(conn, "SELECT s.name, s.description FROM series s;")) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }

    /* Release memory used to store results and get new*/
    mysql_free_result(res);
    res = mysql_use_result(conn);

    /* output fields 1 and 2 of each row */
    while ((row = mysql_fetch_row(res)) != NULL) {
			get_tags(cfg, row[1] + string(" ") + row[2], tags);
		}

    /* Release memory used to store results */
    mysql_free_result(res);

		// create query for tag table
		string tag_table_create("CREATE TABLE `tag` ( `tagname` VARCHAR( 64 ) NOT NULL , "
				"`count` INT UNSIGNED NOT NULL , PRIMARY KEY ( `tagname` ) ) "
				"COMMENT = \"Generated " + timestr() + " by lftagdaemon\";");
				
		// insert values in query
		string values("");
		tagmap::iterator it;
		for ( it=tags.begin(); it != tags.end(); it++ ) {
			// filter
			if (it->second > cfg.tag.minquantity) {
				char escstr[it->first.length()*2+15];
				mysql_real_escape_string(conn, escstr, it->first.c_str(), it->first.length());
				sprintf(escstr, "(\"%s\", \"%d\")", string(escstr).c_str(), it->second);
				values += string(values == "" ? "" : ", ") + escstr;
			}
		}
		string tag_table_insert("INSERT INTO `tag` ( `tagname` , `count` ) VALUES " +  values + ";");

		//cout << tag_table_insert << endl;

		/* insert data */
    if (mysql_query(conn, "START TRANSACTION;")) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }
    if (mysql_query(conn, "DROP TABLE IF EXISTS `tag`;")) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }
    if (mysql_query(conn, tag_table_create.c_str())) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }
    if (mysql_query(conn, tag_table_insert.c_str())) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }
    if (mysql_query(conn, "COMMIT;")) {
			log_error(cfg, mysql_error(conn));
			exit(EXIT_FAILURE);
    }

    /* Close connection */
    mysql_close(conn);

		log_success(cfg, "Tags successful generated.");
		// show content:
		// map<string, unsigned int>::iterator it;
		/*
		for ( it=tags.begin(); it != tags.end(); it++ ) {
			// filter
			if (it->second > cfg.tag.minquantity)
				cout << (*it).first << " => " << (*it).second << endl;
		}
		*/

}


time_t next_runtime(config & cfg) {
	
	// check if there is no next time
	if ( cfg.timer.hours.empty() || cfg.timer.wdays.empty() )
		return 0;

	time_t rawtime;
	time( &rawtime );
	struct tm * timeinfo( localtime( &rawtime ) );

	// first: check the time
	intset::iterator it;
	for ( it = cfg.timer.hours.begin(); it != cfg.timer.hours.end() && *it <= timeinfo->tm_hour; it++ ) {}

	int nexthour;
	if (it == cfg.timer.hours.end())
		nexthour = *(cfg.timer.hours.begin());
	else
		nexthour = *it;

	int startwday( nexthour > timeinfo->tm_hour ? timeinfo->tm_wday : timeinfo->tm_wday + 1 );
	for ( it = cfg.timer.wdays.begin(); it != cfg.timer.wdays.end() && *it < startwday; it++ ) {}

	int nextday;
	if (it == cfg.timer.wdays.end())
		nextday = *(cfg.timer.wdays.begin()) - timeinfo->tm_wday + 7;
	else
		nextday = *it - timeinfo->tm_wday;

	return rawtime + nextday * 24 * 60 * 60 + (nexthour - timeinfo->tm_hour) * 60 * 60 - timeinfo->tm_sec - timeinfo->tm_min * 60;

}


int main(int argc, char ** argv) {

		// set standard configuration
		config cfg;
		set_default_config(cfg);

		if (argc == 2)
			read_config(cfg, argv[1]);

		/* become a daemon */

		/* Our process ID and Session ID */
		pid_t pid, sid;

		/* Fork off the parent process */
		pid = fork();
		if (pid < 0) {
			log_error(cfg, "Could not fork parent process.");
			exit(EXIT_FAILURE);
		}
		/* If we got a good PID, then we can exit the parent process. */
		if (pid > 0) {
			log_success(cfg, "Exit parent process.");
			exit(EXIT_SUCCESS);
		}

		/* Create a new SID for the child process */
		sid = setsid();
		if (sid < 0) {
			log_error(cfg, "Could not create a new SID for the child process.");
			exit(EXIT_FAILURE);
		}

		/* Change the current working directory */
		if ((chdir("/")) < 0) {
			log_error(cfg, "Could not change the current working directory.");
			exit(EXIT_FAILURE);
		}


		// first: do this once
		generate_tags(cfg);

		// second: check if this should run as daemon
		time_t nexttime = next_runtime(cfg);
		while (nexttime > 0) {

			// log next runtime
			string next( ctime( &nexttime ) );
			next.resize(next.length() - 1); // remove \n
			log_success(cfg, "Next runtime is " + next);

			// wait
			while ( time(null) < nexttime ) {
				delay(5000); // sleep 5 seconds
			}
			generate_tags(cfg);
			nexttime = next_runtime(cfg);
		}
		
}
