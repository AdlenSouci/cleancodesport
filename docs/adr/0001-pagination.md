# ADR 0001 - Pagination de la collection des cours

- Statut : accepté
- Date : 2026-11-26

## Contexte

L'endpoint GET /cours doit renvoyer une collection paginee. Il faut choisir une
strategie de pagination coherente avec le contrat OpenAPI, l'adapter HTTP et le
repository PostgreSQL, sans complexifier inutilement un catalogue de cours de
taille modeste.

## Options envisagees

1. Pagination par offset (parametres limit et offset, reponse avec total)
   Avantages : simple a implementer en SQL avec LIMIT et OFFSET, lecture facile
   pour le client, total connu a chaque page.
   Inconvenients : resultats potentiellement instables si des cours sont ajoutes
   ou supprimes pendant la navigation ; OFFSET devient couteux sur de tres
   grandes tables.

2. Pagination par curseur (jeton opaque pointant le dernier element vu)
   Avantages : navigation plus stable face aux insertions, meilleures
   performances en grand volume.
   Inconvenients : plus complexe a concevoir et a expliquer, total exact plus
   difficile a fournir, disproportionne pour ce service a ce stade.

## Decision

Nous retenons la pagination par offset. GET /cours accepte limit (defaut 20,
maximum 100) et offset (defaut 0). La reponse contient items, total, limit et
offset.

## Consequences

Cette decision simplifie le contrat OpenAPI, les routes HTTP et les methodes
paginer / compter du repository. En contrepartie, il faudra surveiller la
stabilite des pages et le cout de OFFSET si le volume de cours augmente
fortement ; un passage au curseur ferait alors l'objet d'un nouvel ADR.
