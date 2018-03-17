import pandas as pd
import pickle
import datetime
import argparse

def run(args):
    # Get the weather forecast for today in CSV (SHOULD BE VIA API)
    weather_today = pd.read_csv(args.weather)
    # Format it to the model input
    day_of_year = (weather_today['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%j"))
    weather_today.insert(loc=1, column='DayOfYear', value=day_of_year)
    month = (weather_today['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%m"))
    weather_today.insert(loc=1, column='Month', value=month)
    weather_today.drop(columns=['Events','Date'], inplace=True)
    # Load the model
    filename = args.model
    loaded_model = pickle.load(open(filename, 'rb'))
    # And predict
    result = loaded_model.predict(weather_today)
    # Then print the predict
    print(result[0])

def main():
	parser=argparse.ArgumentParser(description="Return pollen concentration prediction")
	parser.add_argument("-model",help="model filename" ,dest="model", type=str, required=True)
	parser.add_argument("-todayweather",help="today weather filename" ,dest="weather", type=str, required=True)
	parser.set_defaults(func=run)
	args=parser.parse_args()
	args.func(args)

if __name__=="__main__":
	main()