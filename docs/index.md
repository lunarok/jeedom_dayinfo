# Informations du jour

## Présentation

Ce plugin permet d'obtenir plusieurs types d'équipements qui apportent leurs informations :

### Jour fériés

* jour férié en cours (1 ou 0)

* nombre de jours avant le prochain jour férié

### Informations diverses

* saison actuelle ('Winter', 'Spring', 'Summer', 'Fall')

* nombre de jours avant la prochaine saison

* week-end en cours (1 ou 0)

### Vacances scolaires

* vacances en cours (1 ou 0)

* label vacances (tel que défini dans le calendrier fourni)

* nombre de jours avant les prochaines vacances

* nombre de jours avant la fin des prochaines vacances

* label prochaines vacances

### Lune

* phase de la lune (valeur de 0 à 1, 0 et 1 étant nouvelle lune, la pleine lune 0.5)

* age de la lune (en jours)

* luminosité

* distance

* nom de la phase

### Calendrier personnalisé

* event en cours (1 ou 0)

* label event (tel que défini dans le calendrier fourni)

* nombre de jours avant le prochain event

* nombre de jours avant la fin du prochain event

* label prochain event

## Configuration

Le plugin ne comporte pas de configuration générale.

Sur l'équipement il faut configurer le type et la localisation si elle est nécessaire

Pour le calendrier personnalisé, un champ permet de remplir le chemin, ce fichier doit être local au format ics, le calendrier doit contenir des evenements de type "jours entiers" avec un début et une fin. Par exemple un calendrier de congés, vacances scolaires non pris en charge en standard, jours fériés etc

A partir de la version du 2017-10-08 01:02:33, différents types peuvent nécéssiter la configuration du plugin Localisation et Trajet (geotrav).
La mise à jour depuis des versions antérieures de ce plugin conduisent au non fonctionnement du plugin. Il convient de parametrer les nouvelles options type et localisation. 

## FAQ

> Est-ce que le plugin s'appuie sur des API tiers ?

Oui, le plugin Localisation et Trajet (geotrav) est necéssaire pour  les types jours fériés, vacances scolaires et global. 

A noter, les calendriers des vacances scolaires sont inclus en local.

>Est-ce que je peux récupérer un calendrier depuis une instance Nextcloud ?

Pas directement dans le plugin, mais via ce genre de commande oui :

wget --output-document ./calendar.ics --auth-no-challenge --http-user=USER --http-password="PASSWORD" "http://IP:PORT/remote.php/dav/calendars/USER/Kalender?export"

## Changelog

### Version octobre 2017

Refonte complète avec passage sur différents types d'équipements
