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


void prepare_database( char * db_server, char * db_user, char * db_password, char * db_database ) {

	MYSQL * conn = mysql_init(NULL);

	/* Connect to database */
	if (!mysql_real_connect(conn, db_server, db_user, db_password, db_database, 0, NULL, 0)) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	/* insert data */
	if (mysql_query(conn, "START TRANSACTION;")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}
	if (mysql_query(conn, "DROP TABLE IF EXISTS `plugin_matterhorn-import_tracks`;")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}
	if (mysql_query(conn, "CREATE TABLE `plugin_matterhorn-import_tracks` ("
		"`mediapackage_id` INT UNSIGNED NOT NULL ,"
		" `track_id` INT UNSIGNED NOT NULL ,"
		" `meta_url` VARCHAR( 255 ) NOT NULL ,"
		" PRIMARY KEY ( `mediapackage_id` , `track_id` ) );")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}
	if (mysql_query(conn, "DROP TABLE IF EXISTS `plugin_matterhorn-import_madiapackage`;")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}
	if (mysql_query(conn, "CREATE TABLE `plugin_matterhorn-import_madiapackage` ("
		" `id` INT UNSIGNED NOT NULL ,"
		" `title` VARCHAR( 255 ) NULL ,"
		"`meta_url` VARCHAR( 255 ) NULL ,"
		"PRIMARY KEY ( `id` ) );")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}
	if (mysql_query(conn, "COMMIT;")) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	/* Close connection */
	mysql_close(conn);

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
