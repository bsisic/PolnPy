import argparse
import pandas as pd


def run(args):
    pd.read_csv(args.input).sort_values(by='Date').to_csv(args.output, index=False)


def main():
	parser=argparse.ArgumentParser(description="Sort a csv file by date and flush the input into another csv file")
	parser.add_argument("-in",help="input filename" ,dest="input", type=str, required=True)
	parser.add_argument("-out",help="output filename" ,dest="output", type=str, required=True)
	parser.set_defaults(func=run)
	args=parser.parse_args()
	args.func(args)

if __name__=="__main__":
	main()
