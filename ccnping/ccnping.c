#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <stdbool.h>
#include <sys/time.h>

#include <ccn/ccn.h>
#include <ccn/uri.h>

#define UNUSED(expr) UNUSED_ ## expr __attribute__((unused))

static struct timeval g_start;
static volatile bool g_working;

static enum ccn_upcall_res
incoming_handler(
		struct ccn_closure* UNUSED(selfp),
		enum ccn_upcall_kind kind,
		struct ccn_upcall_info* UNUSED(info))
{
	struct timeval end;
	long mtime, seconds, useconds;

	switch(kind) {
		case CCN_UPCALL_FINAL:
			g_working = false;
			break;

		case CCN_UPCALL_CONTENT_UNVERIFIED:
		case CCN_UPCALL_CONTENT:
			gettimeofday(&end, NULL);

			seconds = end.tv_sec - g_start.tv_sec;
			useconds = end.tv_usec - g_start.tv_usec;
			mtime = (seconds * 1000 + useconds / 1000.0) + 0.5;

			printf("%ld miliseconds\n", mtime);
			break;

		case CCN_UPCALL_INTEREST_TIMED_OUT:
			printf("timeout\n");
			break;

		case CCN_UPCALL_CONTENT_BAD:
			printf("error\n");
			return CCN_UPCALL_RESULT_ERR;

		default:
			fprintf(stderr, "Unexpected response of kind %d\n", kind);
			return CCN_UPCALL_RESULT_ERR;
	}

	return CCN_UPCALL_RESULT_OK;
}

static void
usage(char *argv0)
{
	fprintf(stderr, "Usage: %s <URI>\n", argv0);
	exit(10);
}

int
main(int argc, char *argv[])
{
	int res;
	struct ccn *ccn = NULL;
	struct ccn_charbuf *name = NULL;
	struct ccn_closure *incoming;

	if (argc != 2)
		usage(argv[0]);

	name = ccn_charbuf_create();

	res = ccn_name_from_uri(name, argv[1]);
	if (res < 0) {
		fprintf(stderr, "invalid ccn URI: %s\n", argv[1]);
		usage(argv[0]);
	}
	ccn = ccn_create();

	res = ccn_connect(ccn, NULL);
	if (res < 0) {
		fprintf(stderr, "can't connect to ccn: %d\n", res);
		ccn_perror(ccn, "ccn_connect");
		exit(1);
	}

	incoming = calloc(1, sizeof(*incoming));
	incoming->p = incoming_handler;
	g_working = true;
	gettimeofday(&g_start, NULL);
	res = ccn_express_interest(ccn, name, incoming, NULL);

	while (g_working && res >= 0)
		res = ccn_run(ccn, 100);

	free(incoming);

	if (res < 0) {
		ccn_perror(ccn, "ccn_run");
		exit(1);
	}

	ccn_charbuf_destroy(&name);
	ccn_destroy(&ccn);

	return 0;
}
