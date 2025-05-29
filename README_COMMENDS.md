## Zarejestruj nowego użytkownika
curl -d '{"nick":"NICK","email":"EMAIL","password":"PASSWORD"}' -H "Content-Type: application/json" -X POST http://localhost:8080/api/register

## Pozyskaj JWT
curl -d '{"email":"EMAIL","password":"PASSWORD"}' -H "Content-Type: application/json" -X POST http://localhost:8080/api/login_check

## Usuń użytkownika [WYMAGA UPRAWNIEŃ ROLE_ADMIN]
curl -d '{"uid": "UID"}' -H "Content-Type: application/json" -H "Authorization: Bearer JWT" -X DELETE http://localhost:8080/api/admin/delete

## Edytuj dane zweryfikowanego użytkownika
curl -d '{"password":"PASSWORD","motto":"MOTTO","provenance":"PROVENANCE"}' -H "Content-Type: application/json" -H "Authorization: Bearer JWT" -X PATCH  http://localhost:8080/api/user/me