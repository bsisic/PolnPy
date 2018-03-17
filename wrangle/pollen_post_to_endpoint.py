import requests

url = 'http://localhost:80/insert'
headers = {'Accept' : 'application/json', 'Content-Type' : 'application/json'}
for i in range(1, 34):
    try:
        r = requests.post(url, data=open('pollen.'+str(i)+'.json', 'rb'), headers=headers)
    except requests.exceptions.HTTPError as e:
        print(e)
        sys.exit(1)