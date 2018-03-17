import pandas as pd
from numpy import concatenate
from pandas import read_csv
from pandas import DataFrame
from pandas import concat
from keras.models import Sequential
from keras.layers import Dense
from keras.layers import LSTM
from keras.models import model_from_json
import pickle
import datetime
import argparse

# convert series to supervised learning
def series_to_supervised(data, n_in=1, n_out=1, dropnan=True):
    n_vars = 1 if type(data) is list else data.shape[1]
    df = DataFrame(data)
    cols, names = list(), list()
    # input sequence (t-n, ... t-1)
    for i in range(n_in, 0, -1):
        cols.append(df.shift(i))
        names += [('var%d(t-%d)' % (j+1, i)) for j in range(n_vars)]
    # forecast sequence (t, t+1, ... t+n)
    for i in range(0, n_out):
        cols.append(df.shift(-i))
        if i == 0:
            names += [('var%d(t)' % (j+1)) for j in range(n_vars)]
        else:
            names += [('var%d(t+%d)' % (j+1, i)) for j in range(n_vars)]
    # put it all together
    agg = concat(cols, axis=1)
    agg.columns = names
    # drop rows with NaN values
    if dropnan:
        agg.dropna(inplace=True)
    return agg

def run(args):
    # Get the weather forecast for today in CSV (SHOULD BE VIA API)
    data_for_predict = read_csv('weather_today_for_LSTM.csv')
    data_for_predict = data_for_predict[['Date',args.pollentype,'DayOfYear','TempMax','HumidMin','VisibilityAvg']]
    data_for_predict.set_index('Date',inplace=True)

    n_days = 7
    n_features = 4
    n_obs = n_days * n_features

    # frame as supervised learning
    reframed_for_predict = series_to_supervised(data_for_predict, n_days, 1)
    # drop columns we don't want to predict
    reframed_for_predict.drop(reframed_for_predict.columns[[6,7,8,9]], axis=1, inplace=True)
    # split into train and test sets
    values_for_predict = reframed_for_predict.values
    # split into input and outputs
    to_predict_X, to_predict_y = values_for_predict[:, :n_obs], values_for_predict[:, -n_features]
    # reshape input to be 3D [samples, timesteps, features]
    to_predict_X = to_predict_X.reshape((to_predict_X.shape[0], n_days, n_features))

    # load json and create model
    json_file = open('model_'+args.pollentype+'.json', 'r')
    loaded_model_json = json_file.read()
    json_file.close()
    loaded_model = model_from_json(loaded_model_json)
    # load weights into new model
    loaded_model.load_weights('LSTM_model_'+args.pollentype+'.h5')

    # make a prediction
    result = loaded_model.predict(to_predict_X)
    # Then print the predict
    print(list(result[0])[0])

def main():
	parser=argparse.ArgumentParser(description="Return pollen concentration prediction")
	parser.add_argument("-pollentype",help="type of pollen" ,dest="pollentype", type=str, required=True)
	parser.set_defaults(func=run)
	args=parser.parse_args()
	args.func(args)

if __name__=="__main__":
	main()