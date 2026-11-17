-- Jalankan sekali pada database PUTU yang sudah memiliki tabel public.users.
-- Tabel bot hanya diakses backend PDO; jangan expose schema putu_bot ke Data API.
BEGIN;

CREATE SCHEMA putu_bot;
REVOKE ALL ON SCHEMA putu_bot FROM PUBLIC;

CREATE TABLE putu_bot.accounts (
    id_user BIGINT PRIMARY KEY REFERENCES public.users(id_user) ON DELETE CASCADE,
    chat_id BIGINT UNIQUE CHECK (chat_id > 0),
    connect_hash CHAR(64) UNIQUE,
    connect_expires TIMESTAMPTZ,
    connected_at TIMESTAMPTZ,
    reminder_hours SMALLINT NOT NULL DEFAULT 24 CHECK (reminder_hours BETWEEN 1 AND 168),
    reminders_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    news_enabled BOOLEAN NOT NULL DEFAULT TRUE,
    CHECK ((connect_hash IS NULL) = (connect_expires IS NULL)),
    CHECK ((chat_id IS NULL) = (connected_at IS NULL))
);

CREATE TABLE putu_bot.deliveries (
    id_user BIGINT NOT NULL REFERENCES putu_bot.accounts(id_user) ON DELETE CASCADE,
    kind TEXT NOT NULL CHECK (kind IN ('deadline', 'pengumuman')),
    source_id BIGINT NOT NULL,
    version TEXT NOT NULL,
    sent_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user, kind, source_id, version)
);

CREATE TABLE putu_bot.state (
    singleton BOOLEAN PRIMARY KEY DEFAULT TRUE CHECK (singleton),
    update_offset BIGINT NOT NULL DEFAULT 0 CHECK (update_offset >= 0)
);
INSERT INTO putu_bot.state DEFAULT VALUES;

ALTER TABLE putu_bot.accounts ENABLE ROW LEVEL SECURITY;
ALTER TABLE putu_bot.deliveries ENABLE ROW LEVEL SECURITY;
ALTER TABLE putu_bot.state ENABLE ROW LEVEL SECURITY;
REVOKE ALL ON ALL TABLES IN SCHEMA putu_bot FROM PUBLIC;

CREATE INDEX idx_tugas_deadline_bot ON public.tugas(deadline);
CREATE INDEX idx_pengumuman_kelas_created_bot ON public.pengumuman(id_kelas, created_at);

COMMIT;
