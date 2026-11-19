-- Apply once, after TELEGRAM.sql. Existing receipts belong to Telegram.
BEGIN;
ALTER TABLE putu_bot.accounts
    ADD COLUMN wa_chat TEXT UNIQUE CHECK (wa_chat ~ '^[0-9]{5,30}@(c\.us|lid)$'),
    ADD COLUMN wa_sender TEXT CHECK (wa_sender ~ '^[0-9]{8,15}$'),
    ADD COLUMN wa_hash CHAR(64) UNIQUE,
    ADD COLUMN wa_expires TIMESTAMPTZ,
    ADD COLUMN wa_connected_at TIMESTAMPTZ,
    ADD COLUMN web_subscription JSONB,
    ADD COLUMN web_session CHAR(64),
    ADD COLUMN web_connected_at TIMESTAMPTZ,
    ADD CHECK ((wa_hash IS NULL) = (wa_expires IS NULL)),
    ADD CHECK ((wa_chat IS NULL) = (wa_connected_at IS NULL)),
    ADD CHECK ((wa_chat IS NULL) = (wa_sender IS NULL)),
    ADD CHECK ((web_subscription IS NULL) = (web_session IS NULL)),
    ADD CHECK ((web_subscription IS NULL) = (web_connected_at IS NULL));
CREATE UNIQUE INDEX putu_bot_web_endpoint ON putu_bot.accounts ((web_subscription->>'endpoint'));
ALTER TABLE putu_bot.deliveries
    ADD COLUMN channel TEXT NOT NULL DEFAULT 'telegram' CHECK (channel IN ('telegram', 'whatsapp', 'web')),
    DROP CONSTRAINT deliveries_pkey,
    ADD PRIMARY KEY (id_user, kind, source_id, version, channel);
COMMIT;
