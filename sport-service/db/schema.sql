-- Schéma sport-service. Après modification :
-- docker compose down -v && docker compose up -d

CREATE TABLE IF NOT EXISTS cours (
  id         UUID PRIMARY KEY,
  intitule   VARCHAR(200) NOT NULL,
  places     INTEGER NOT NULL CHECK (places >= 1),
  debut      TIMESTAMPTZ NOT NULL,
  version    INTEGER NOT NULL DEFAULT 1 CHECK (version >= 1)
);

-- Participants d'un cours : inscrits ou liste d'attente (ordre = position).
CREATE TABLE IF NOT EXISTS inscriptions (
  cours_id       UUID NOT NULL REFERENCES cours (id) ON DELETE CASCADE,
  participant_id UUID NOT NULL,
  statut         VARCHAR(20) NOT NULL
                   CHECK (statut IN ('inscrit', 'en_liste_attente')),
  position       INTEGER NOT NULL CHECK (position >= 1),
  PRIMARY KEY (cours_id, participant_id)
);

CREATE INDEX IF NOT EXISTS idx_inscriptions_cours_statut_position
  ON inscriptions (cours_id, statut, position);
