import { ErreurCapaciteInvalide } from './erreurs';

export class Capacite {
  private constructor(private readonly _valeur: number) {}

  public static creer(valeur: number): Capacite {
    if (!Number.isInteger(valeur) || valeur <= 0) {
      throw new ErreurCapaciteInvalide();
    }
    return new Capacite(valeur);
  }

  public valeur(): number {
    return this._valeur;
  }

  public estEgalA(autre: Capacite): boolean {
    return this._valeur === autre._valeur;
  }
}