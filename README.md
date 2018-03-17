PolnPy
======
*I will try to update this README file from time to time during the GameOfCode hackathon*

The idea: Get pollen historical data in Luxembourg and make pollen concentration  **predictions**

SWATEC (Scrape, Wrangle, Analyze, Train and then, Expose, Consume)

## Scrape

What:
- Srape data as neither API nor data set available

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

Libraries used:
- Pandas https://pandas.pydata.org/
- Matplotlib https://matplotlib.org/

## Train

What:

Train and test a couple of models
- Random Forest Regressor?
- One more, Support Vector Regressor?
- LSTM?

Libraries used:
- Pandas https://pandas.pydata.org/
- Scikit Learn http://scikit-learn.org
- TensorFlow? https://www.tensorflow.org/
- Keras? https://keras.io/
- Prophet https://facebook.github.io/prophet/

## Expose

What:

2 Restful API endpoints:
- One to get historical data (all pollen types since 1996)
- One to make predictions for same day and next day (only ambrosia, betula and graminea)
- And just a small helper to get the list of supported pollens

...

## Consume

What:

Front end

...