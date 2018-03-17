import pandas as pd
import pickle
import datetime

# Get the weather forecast for today in CSV (SHOULD BE VIA API)
weather_today = pd.read_csv('weather_today.csv')
# Format it to the model input
day_of_year = (weather_today['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%j"))
weather_today.insert(loc=1, column='DayOfYear', value=day_of_year)
month = (weather_today['Date']).apply(lambda x: datetime.datetime.strptime(x, "%Y-%m-%d").strftime("%m"))
weather_today.insert(loc=1, column='Month', value=month)
weather_today.drop(columns=['Events','Date'], inplace=True)
# Load the model
filename = 'RFR_model.sav'
loaded_model = pickle.load(open(filename, 'rb'))
# And predict
result = loaded_model.predict(weather_today)
# Then print the predict
print(result[0])