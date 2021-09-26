FROM python:3

RUN mkdir /app

ADD update_ip.py /app/
ADD requirements.txt /app/
ADD wsgi.py /app/

RUN pip3 install -r /app/requirements.txt

WORKDIR /app

EXPOSE 80

CMD [ "gunicorn", "-w", "2", "-b", "0.0.0.0:80", "wsgi:app" ]
