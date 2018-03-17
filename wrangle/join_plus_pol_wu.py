import argparse
import datetime
import pandas as pd
import numpy as np


def run(args):
    pol_df = pd.read_csv(args.pollen)
    pol_df.set_index('Date', inplace=True)
    wu_df = pd.read_csv(args.wunderground)
    wu_df.set_index('Date', inplace=True)

    # Join pollen data and wu data
    pol_wu_df  = pol_df.join(wu_df, how='outer')

    # Eliminate all raws where there is no weather data
    pol_wu_df = pol_wu_df[np.isfinite(pol_wu_df['TempMax'])]

    # Put a zero for NaN pollen data point
    # Indeed, pollen.lu, until recently, did not provide data points from Oct 1 to Dec 31
    # Assumption: no pollen over that period...
    pol_wu_df[list(pol_wu_df)[0:33]] = pol_wu_df[list(pol_wu_df)[0:33]].fillna(value=0)

    # Insert 3 new columns: 'Year', 'Month', 'DayOfYear'
    pol_wu_df.reset_index(inplace=True)
    day_of_year = (pol_wu_df['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%j"))
    pol_wu_df.insert(loc=1, column='DayOfYear', value=day_of_year)
    month = (pol_wu_df['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%m"))
    pol_wu_df.insert(loc=1, column='Month', value=month)
    year = (pol_wu_df['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%Y"))
    pol_wu_df.insert(loc=1, column='Year', value=year)

    pol_wu_df.to_csv(args.output, index=False)


def main():
	parser=argparse.ArgumentParser(description="Join pollen csv file and wunderground csv file")
	parser.add_argument("-pol",help="input filename" ,dest="pollen", type=str, required=True)
	parser.add_argument("-wu",help="input filename" ,dest="wunderground", type=str, required=True)
	parser.add_argument("-out",help="output filename" ,dest="output", type=str, required=True)
	parser.set_defaults(func=run)
	args=parser.parse_args()
	args.func(args)

if __name__=="__main__":
    main()