Uruchomienie:

```
docker compose up -d
docker exec -it php-jdg-checker composer install
docker exec -it php-jdg-checker bin/console app:jdg-checker ŚCIEŻKA_DO_PLIKU_CSV
```
W pliku .env jest zmienna `CEIDG_TOKEN`. Trzeba tam dodać token pozyskany z CEIDG. Formularz do uzyskania dostępi do api: https://akademia.biznes.gov.pl/hurtownia-danych-instrukcje-i-dokumentacja/

Ze względu na limity api, jeden request leci co 4 sekundy.
