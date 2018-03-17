PolnPy
======
*I will try to update this README file from time to time during the GameOfCode hackathon*

The idea: Get pollen historical data in Luxembourg and make pollen concentration  **predictions**

SWAT-EC (Scrape, Wrangle, Analyze, Train and then, Expose, Consume)

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
- Well, actually, better to look at it from a multivariate time series perspective

Libraries used:
- Pandas https://pandas.pydata.org/
- Matplotlib https://matplotlib.org/
- Seaborn https://seaborn.pydata.org/

Move the output file from previous step in this directory and...

`jupyter notebook PolnPyAnalysis.ipynb`
(and run through all steps of the notebook)

## Train

What:

Train and test a couple of models
- Random Forest Regressor
- One more, Support Vector Regressor? *[no time]*
- LSTM

Libraries used:
- Pandas https://pandas.pydata.org/
- Scikit Learn http://scikit-learn.org
- TensorFlow https://www.tensorflow.org/
- Keras? https://keras.io/

Put file from previous step in this directory and...

`jupyter notebook PolnPyRFR.ipynb`
(and then run through all steps of the notebook)

`jupyter notebook PolnPyLSTM.ipynb`
(and then run through all steps of the notebook)

Model files

## Expose

What:

2 Restful API endpoints:
- One to get historical data (all pollen types since 1996)
- One to make predictions for same day and next day (only ambrosia, betula and graminea)
- And just a small helper to get the list of supported pollens

Libraries used:
- Flask http://flask.pocoo.org/
- FlaskRESTful https://flask-restful.readthedocs.io
- FlaskSQLAlchemy http://flask-sqlalchemy.pocoo.org

`python app.py` + `ngrok http 4000` to expose externally

*The API is deployable on heroku but carefull, 
1. Heroku Postgres DB needed (how to upload data:* `heroku pg:push data.db HEROKU_POSTGRESQL -app`*)
2. As the git repo contains all 6 directories, it is necessary to deploy as subtree* `git subtree push --prefix expose heroku master`

## Consume

What:

No time to develop a proper front end so... let's get some help from Alexa...
A simple Alexa skill to get historical data and predictions as per available APIs
+ an Easter egg

Libraries used:
- Flask-Ask http://flask-ask.readthedocs.io/en/latest/
- Tool used to generate utterance combinations: http://alexa-utter-gen.paperplane.io/

`python app.py` + `ngrok http 5000`

*Deployable to AWS Lambda with zapp*