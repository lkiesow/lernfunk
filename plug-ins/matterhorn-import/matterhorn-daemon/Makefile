COMPILER = gcc
FLAGS    = -Wall
INCLUDE  = -I/usr/include/mysql
LIBPATH  = -L/usr/lib64/mysql
LIB      = -lmysqlclient

it: lfmatterhorn-daemon.c
	$(COMPILER) $(FLAGS) $(INCLUDE) $(LIBPATH) $(LIB) lfmatterhorn-daemon.c -o lfmatterhorn-daemon

test:
	./lfmatterhorn-daemon

clean:
	rm -f lfmatterhorn-daemon *.log *~
