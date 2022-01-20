import requests

#Get CSRF token
token = str(requests.get('http://127.0.0.1:8088/session/token').text)

endpoint = 'http://localhost:8088/node?_format=hal_json'

#Set all required headers
headers = {'Content-Type':'application/hal+json',
    'X-CSRF-Token':token
}

#Include all fields required by the content type
payload =  '''{
    "_links": {
      "type": {
        "href": "http://localhost/drupal-9/student-registration"
      }
    },
    "student_mail":[{"value":"abc"}],
    "student_phone":[{"value":"abc@example.com"}]
    }'''

#Post the new node (a Contact) to the endpoint.
r = requests.post(endpoint, data=payload, headers=headers, auth=('foo','foo'))

#Check was a success 
if r.status_code == 201:
    print "Success"
else:
    print "Fail"
    print r.status_code