COMPILER = g++
FLAGS    = -Wall
INCLUDE  = -I/usr/include/mysql
LIBPATH  = -L/usr/lib64/mysql
LIB      = -lmysqlclient

it: lftagdaemon.cpp
	$(COMPILER) $(FLAGS) $(INCLUDE) $(LIBPATH) $(LIB) lftagdaemon.cpp -o lftagdaemon

test:
	./lftagdaemon

clean:
	rm -f lftagdaemon *.log *~
