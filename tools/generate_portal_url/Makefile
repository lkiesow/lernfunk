COMPILER = gcc
FLAGS    = -Wall
INCLUDE  = -I/usr/include/mysql
LIBPATH  = -L/usr/lib64/mysql
LIB      = -lmysqlclient

it: lfgenerateportalurl.c
	$(COMPILER) $(FLAGS) $(INCLUDE) $(LIBPATH) $(LIB) lfgenerateportalurl.c \
		-o lfgenerateportalurl

test:
	./lfgenerateportalurl

clean:
	rm -f lfgenerateportalurl *.log *~
