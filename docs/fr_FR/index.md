Plugin Surveillance Equipement (id : ethalsurveillance) Description
===
Plugin servant à la surveillance d’un équipement.

Présentation
===
La surveillance de l’équipement est faite à partir d’une commande ***Lo*** (ie etat,…) ou d’une commande de mesure ***analogique*** (puissance,temperature,…)

Il permet de connaître le temps où la commande de l’équipement est active,+ il gère une commande d’alarme, en fonction d’un temps minimun et/ou maximun où la commande de l’équipement est active, d’une heure prévue où la commande de l’équipement est inactif ou actif, d’une valeur haute sur un compteur,ces paramètres sont disponible pour une gestion hebdomadaire.

Un code d’alarme, permet de connaître la cause de l’alarme,
Un compteur de cycle d’activité de l’équipement,
Des actions peuvent etre configurer en fonction de la valeur des commandes Etat et Alarme.

Ci dessous des exemples de widget.

![utilisation1-widget](../images/utilisation1-widget.png)

![utilisation2-widget](../images/utilisation2-widget.png)

![utilisation3-widget](../images/utilisation3-widget.png)

Un panel pour la visualisation graphique du temps d’activité de l’équipement est disponible depuis le menu Acceuil→Surveillance Equipement

![panel](../images/panel.png)

Configuration
===

Onglet Equipement
====
  - Type de commande : Type de la commande qui servira à surveiller l’équipement , Logique ou Analogique,

  - Commande de l’équipement à surveiller de type "Logique"
  ![equipement logique](../images/equipement-logique.png)
  
  - ***Commande équipement*** : Commande d’état de l’équipement à surveiller

Inverser : Inversion de la commande d’état de l’équipement à surveiller

Temps mini surveillance active (min) : Temps de surveillance minimun de l’équipement pour générer une alarme, laisser vide pour ne pas générer d’alarme

Temps max surveillance active (min) : Temps de surveillancet maximun de l’équipement pour générer une alarme, laisser vide pour ne pas générer d’alarme

Heure prévue surveillance inactive (HHMM) : heure à laquelle l’équipement (ie la commande Etat) est prévu d’être à OFF, laisser vide pour ne pas générer d’alarme

Heure prévue surveillance active (HHMM) : heure à laquelle l’équipement (ie la commande Etat) est prévu d’être à ON, laisser vide pour ne pas générer d’alarme

Valeur compteur haut : Valeur haute du compteur pour générer une alarme, laisser vide pour ne pas générer d’alarme
