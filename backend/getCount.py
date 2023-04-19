import sys
def usage()
    print(sys.argv[0] + " ")
try:
    name = sys.argv[1]
except:
    usage()
    exit(1)
with open(sys