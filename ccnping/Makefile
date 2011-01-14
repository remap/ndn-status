CC = gcc
LD = gcc -pipe

BDECFLAGS=      -W -Wall -ansi -pedantic -Wbad-function-cast -Wcast-align \
                -Wcast-qual -Wchar-subscripts -Winline \
                -Wmissing-prototypes -Wnested-externs -Wpointer-arith \
                -Wredundant-decls -Wshadow -Wstrict-prototypes -Wwrite-strings \
                -std=c99 -Wextra

#STRIP=touch
#CFLAGS = -pipe -g3
#CFLAGS += $(BDECFLAGS)

STRIP=strip
CFLAGS = -pipe

CCNX_DIR = ~/ccnx
INCLUDES = -I $(CCNX_DIR)/include
LIBS = $(CCNX_DIR)/lib/libccn.a -lcrypto

SRCS_PING = ccnping.c
PROG_PING = ccnping

OBJS_PING = ${SRCS_PING:.c=.o}

all: $(PROG_PING)

$(PROG_PING): $(OBJS_PING)
	$(LD) -o $@ $(OBJS_PING) $(LIBS)
	$(STRIP) $(PROG_PING)

rmtmp:
	$(RM) *.o

clean: rmtmp
	$(RM) $(PROG_PING)

.c.o:
	$(CC) $(CFLAGS) $(INCLUDES) -c $<

.PHONY: all rmtmp clean
