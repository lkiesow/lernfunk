COMPILER = gcc
FLAGS    = -Wall
INCLUDE  = -I/usr/include/mysql
LIBPATH  = -L/usr/lib64/mysql
LIB      = -lmysqlclient -lcurl

it: lflinkchecker.c
	$(COMPILER) $(FLAGS) $(INCLUDE) $(LIBPATH) $(LIB) lflinkchecker.c -o lflinkchecker

test:
	./lftagdaemon

clean:
	rm -f lflinkchecker *.log *~
