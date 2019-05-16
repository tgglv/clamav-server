### ClamAV Server

Simple API for scanning zip archives with the use of ClamAV.

#### How to run

```bash
# Run in project's root folder: 
$ docker run -d -p 9090:80 --name my-clamav-server timurns/clamav-server:latest
```

#### API Methods

##### Health Check

```http request
GET /v1/health-check.php
```

Returns: `200 OK`.

Sample usage:

```bash
curl http://localhost:9090/v1/health-check.php
```

##### Scan for viruses

```http request
POST /v1/scan.php
```

Response:

If file is _infected_, it will return: 
```http request
400 Bad Request

Body:
{
  "success": false,
  "message": "File is infected"
}
```

If file _isn't infected_, it will return:
```http request
200 OK

Body:
{
  "success": true,
  "message": "File is clean"
}
```

Sample usage:

```bash
curl --form 'file=@/path/to/file.zip' http://localhost:9090/v1/scan.php
```