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

#include <mysql.h>
#include "stdlib.h"
#include "stdio.h"
#include "termios.h"
#include "unistd.h"
#include "string.h"
#include "curl/curl.h"



int check_url( CURL * curl, char * curlerr, char * fmtstr, char * id, char * title, char * url ) {

	if ( url && strcmp( url, "" ) ) {

		CURLcode cres;
		curl_easy_setopt( curl, CURLOPT_URL, url );
		cres = curl_easy_perform( curl );
		if ( cres ) {
			printf( "ERROR (%d): %s\n", cres, curlerr );
			printf( fmtstr, id, title, url );
			return 0;
		}

		long int rcode;
		curl_easy_getinfo( curl, CURLINFO_RESPONSE_CODE, &rcode );
		if ( rcode != 200 ) {
			printf( "WARNING: HTTP response code is %ld\n", rcode );
			printf( fmtstr, id, title, url );
			return 0;
		}
	}

	return 1;

}



void prepare_database( char * db_server, char * db_user, char * db_password, char * db_database ) {

	MYSQL *  conn = mysql_init(NULL);
	CURL *   curl = curl_easy_init();
	char curlerr[CURL_ERROR_SIZE];

	if ( !curl ) {
		fprintf( stderr, "ERROR: could not initialize curl\n" );
		exit(EXIT_FAILURE);
	}
	curl_easy_setopt( curl, CURLOPT_NOBODY, 1 );
	curl_easy_setopt( curl, CURLOPT_ERRORBUFFER, curlerr );

	/* Connect to database */
	if (!mysql_real_connect(conn, db_server, db_user, db_password, db_database, 0, NULL, 0)) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	/**** check mediaobject ***************************************************/
	printf( "checking series...\n\n" );

	if (mysql_query(conn, "select series_id, name, thumbnail_url, add_url "
		"from series where thumbnail_url != '' or add_url != ''")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	MYSQL_ROW row;
	MYSQL_RES * res = mysql_use_result(conn);

	/* output fields 1 and 2 of each row */
	while ((row = mysql_fetch_row(res)) != NULL) {
		check_url( curl, curlerr, "Series %s: %s\nthumbnail_url: %s\n\n", row[0], row[1], row[2] );
		check_url( curl, curlerr, "Series %s: %s\nadd_url: %s\n\n", row[0], row[1], row[3] );
	} 



	/**** check series ********************************************************/
	printf( "checking mediaobjects...\n\n" );

	if (mysql_query(conn, "select object_id, title, url, thumbnail_url, "
		"preview_url, image_url, add_url from mediaobject")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	/* Release memory used to store results and get new*/
	mysql_free_result(res);
	res = mysql_use_result(conn);

	/* output fields 1 and 2 of each row */
	while ((row = mysql_fetch_row(res)) != NULL) {
		check_url( curl, curlerr, "Mediaobject %s: %s\nurl: %s\n\n",           row[0], row[1], row[2] );
		check_url( curl, curlerr, "Mediaobject %s: %s\nthumbnail_url: %s\n\n", row[0], row[1], row[3] );
		check_url( curl, curlerr, "Mediaobject %s: %s\npreview_url: %s\n\n",   row[0], row[1], row[4] );
		check_url( curl, curlerr, "Mediaobject %s: %s\nimage_url: %s\n\n",     row[0], row[1], row[5] );
		check_url( curl, curlerr, "Mediaobject %s: %s\nadd_url: %s\n\n",       row[0], row[1], row[6] );
	} 


	/* Close connection */
	mysql_close(conn);
	curl_easy_cleanup(curl);

}


int main(int argc, char ** argv) {

	char db_server[512];
	char db_database[512];
	char db_user[512];
	char db_password[512];

	printf( "Prepare Lernfunk database for lernfunk matterhorn-import plugin...\n" );

	printf( "\tServer: " );
	scanf( "%s", db_server );

	printf( "\tDatabase: " );
	scanf( "%s", db_database );

	printf( "\tUser: " );
	scanf( "%s", db_user );

	/* disable output for password */
	struct termios old_opts, new_opts;
	tcgetattr( STDIN_FILENO, &old_opts );
	memcpy( &new_opts, &old_opts, sizeof( struct termios ) );
	new_opts.c_lflag &= ~( ECHO | ECHOE | ECHOK | ECHONL | ECHOPRT | ECHOKE );
	tcsetattr( STDIN_FILENO, TCSANOW, &new_opts );

	printf( "\tPassword: " );
	scanf( "%s", db_password );

	/* reset terminal settings */
	tcsetattr( STDIN_FILENO, TCSANOW, &old_opts );
	printf( "\n" );

	prepare_database( db_server, db_user, db_password, db_database );

	return EXIT_SUCCESS;
		
}
