## Zarejestruj nowego użytkownika
curl -d '{"nick":"NICK","email":"EMAIL","password":"PASSWORD"}' -H "Content-Type: application/json" -X POST http://localhost:8080/api/register

## Pozyskaj JWT i referesh token
curl -c - -d '{"email":"beta@gamma.delta","password":"kwakwa5!"}' -H "Content-Type: application/json" -X POST http://localhost:8080/api/login_check

## Odśwież JWT
curl -c - -d '{"refresh_token"="REFRESH_TOKEN"}' -H "Content-Type: application/json" -X POST http://localhost:8080/api/token_refresh

## Usuń użytkownika [WYMAGA UPRAWNIEŃ ROLE_ADMIN]
curl -d '{"uid": "UID"}' -H "Content-Type: application/json" -H "Authorization: Bearer JWT" -X DELETE http://localhost:8080/api/admin/delete

## Edytuj dane zweryfikowanego użytkownika
curl -d '{"password":"PASSWORD","motto":"MOTTO","provenance":"PROVENANCE"}' --cookie "BEARER=JWT" -H "Content-Type: application/json" -X PATCH  http://localhost:8080/api/user/me