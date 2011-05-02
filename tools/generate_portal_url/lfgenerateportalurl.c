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

char * make_url( char * name, char * portal_url ) {

	int i;
	char * urlchar = portal_url;

	for ( i = 0; i < strlen( name ); i++ ) {
		if ( ( name[i] >= '0' && name[i] <= '9') 
				|| ( name[i] >= 'A' && name[i] <= 'Z' ) 
				|| ( name[i] >= 'a' && name[i] <= 'z' ) 
				|| ( name[i] == '_') ) {
			*urlchar = name[i];
			urlchar++;
		} else if ( name[i] == ' ' ) {
			*urlchar = '_';
			urlchar++;
		}
	}
	*urlchar = 0;
	return portal_url;

}


void prepare_database( char * db_server, char * db_user, char * db_password, char * db_database ) {

	MYSQL *  conn = mysql_init(NULL);

	/* Connect to database */
	if (!mysql_real_connect(conn, db_server, db_user, db_password, db_database, 0, NULL, 0)) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	/**** check mediaobject ***************************************************/
	printf( "checking series...\n\n" );

	if (mysql_query(conn, "select series_id, name from series "
				"where portal_url = ''" )) {
		fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
		exit(EXIT_FAILURE);
	}

	MYSQL_ROW row;
	MYSQL_RES * res = mysql_use_result(conn);
	char portal_url[250];
	char * query[1000];
	char ** current_query = query;
	int addquerylen = strlen( "UPDATE series SET portal_url = '' "
			"WHERE series_id = '' LIMIT 1" );

	/* output fields 1 and 2 of each row */
	while ((row = mysql_fetch_row(res)) != NULL) {
		make_url( row[1], portal_url );
		printf( "%4s: %s -> %s\n", row[0], row[1], portal_url );
		*current_query = (char *) malloc( sizeof( char ) 
			* ( strlen( portal_url ) + strlen( row[0] ) + addquerylen + 1 ) );
		sprintf( *current_query, "UPDATE series SET portal_url = '%s' "
			"WHERE series_id = '%s' LIMIT 1", portal_url, row[0] );
		current_query++;
	} 

	current_query--;
	printf( "---\n" );
	fflush( stdout );

	for ( ; current_query >= query; current_query-- ) {
	printf( "%s\n", *current_query );
		if ( mysql_query( conn, *current_query ) ) {
			fprintf( stderr, "ERROR: %s\n", mysql_error(conn) );
			fprintf( stderr, "%s\n", *current_query );
			fflush( stderr );
			exit(EXIT_FAILURE);
		}
		free( *current_query );
	}
	printf( "“portal_url” fields filled successfully…\n" );

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
