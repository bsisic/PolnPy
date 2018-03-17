PolnPy
======
*We will try to update this README file from time to time during the GameOfCode hackathon*

The idea: Get pollen historical data in Luxembourg and make pollen concentration  **predictions**

SWATEC (Scrape, Wrangle, Analyze, Train and then, Expose, Consume)

## Scrape

What:
- Scrape data as neither API nor data set available

Data sources:
- pollen.lu, 26+ years of data (since Jan 1, 1992)
- wunderground.com, almost 22 years of data (since Jul 1, 1996)

Libraries used:
- Scrapy https://scrapy.org/


`scrapy crawl pol_arch -o pol.csv`

`scrapy crawl wus -o wu.csv`

## Wrangle

What:
- Cleanse
- Sort
- Join

Libraries used
- NumPy http://www.numpy.org/
- Pandas https://pandas.pydata.org/

Move the files from previous step in this directory and...

`python sort_bydate.py -in pol.csv -out pol_sorted.csv`

`python sort_bydate.py -in wu.csv -out wu_sorted.csv`

`python join_plus_pol_wu -pol pol_sorted.csv -wu wu_sorted.csv -out pol_wu.csv`

## Analyze

What:
- Discover and visualize data
- Try to identify some correlations
- Conclusion, it is very much a time series case (good candidate for Random Forest Regressor, FB Prophet, LSTM)

Libraries used:
- Pandas https://pandas.pydata.org/
- Matplotlib https://matplotlib.org/

`jupyter notebook PolnPyAnalysis.ipynb` and run all cells

## Train

What:

Train and test a couple of models
- Random Forest Regressor, pretty interesting result!
- Prophet, good result but the range between lower estimate and higher estimate is quite large
- LSTM, best result, our production is running on that model

Libraries used:
- Pandas https://pandas.pydata.org/
- Scikit Learn http://scikit-learn.org
- Prophet https://facebook.github.io/prophet/
- TensorFlow https://www.tensorflow.org/
- Keras https://keras.io/

`jupyter notebook PolnPyRandomForest.ipynb` and run through all steps

[Dirty solution to be refactored...]

The same day forecast (`weather_today.csv`), the model (`RFR_model.sav`) and the `consume_model.py` script was tested in the backend

`jupyter notebook PolnPyProphet.ipynb` and run through all steps to see the results

`jupyter notebook PolnPyLSTM.ipynb` and run through all steps

[Dirty solution to be refactored...]

The same day forecast (`weather_today_for_LSTN.csv`), the model (`LSTM_model.h5`), the model json (`model.json`) and the `consume_LSTM_model.py` script will be used by the backend

## Expose

What:

2 Restful API endpoints:
- One to get historical data (all pollen types since 1996)
- One to make predictions for same day and next day (only ambrosia, betula and graminea)
- And just a small helper to get the list of supported pollens

Docker

Symphony

Redis

MongoDB

## Consume

What:

Front end

...