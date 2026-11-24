export class ErreurCapaciteInvalide extends Error {
  constructor() {
    super('La capacité doit être un nombre entier strictement positif.');
    this.name = 'ErreurCapaciteInvalide';
  }
}

export class ErreurParticipantDejaInscrit extends Error {
  constructor() {
    super('Ce participant est déjà inscrit ou en liste d attente pour ce cours.');
    this.name = 'ErreurParticipantDejaInscrit';
  }
}