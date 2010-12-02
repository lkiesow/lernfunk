COMPILER = gcc
FLAGS    = -Wall
INCLUDE  = -I/usr/include/mysql
LIBPATH  = -L/usr/lib64/mysql
LIB      = -lmysqlclient

it: lfmatterhorn-install.c
	$(COMPILER) $(FLAGS) $(INCLUDE) $(LIBPATH) $(LIB) lfmatterhorn-install.c -o lfmatterhorn-install

test:
	./lftagdaemon

clean:
	rm -f lfmatterhorn-install *.log *~
