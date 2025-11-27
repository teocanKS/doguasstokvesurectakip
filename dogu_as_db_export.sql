--
-- PostgreSQL database dump
--

\restrict DjaFknSi0i4J1VBxOa07IbaeeM574fbqv3YzoRi2Ys83mO8gMjw9Uuf8byoTeJA

-- Dumped from database version 14.20 (Homebrew)
-- Dumped by pg_dump version 14.20 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: fn_log_islem(); Type: FUNCTION; Schema: public; Owner: teocan
--

CREATE FUNCTION public.fn_log_islem() RETURNS trigger
    LANGUAGE plpgsql
    AS $_$
DECLARE
    v_old jsonb;
    v_new jsonb;
    v_key bigint;
    key_col text;
BEGIN
    -- Determine which column is the primary key for this table from trigger arguments
    IF TG_NARGS >= 1 THEN
        key_col := TG_ARGV[0];
    ELSE
        key_col := 'id';
    END IF;

    IF TG_OP = 'INSERT' THEN
        v_old := NULL;
        v_new := to_jsonb(NEW);
        EXECUTE format('SELECT ($1).%I', key_col) INTO v_key USING NEW;
    ELSIF TG_OP = 'UPDATE' THEN
        v_old := to_jsonb(OLD);
        v_new := to_jsonb(NEW);
        EXECUTE format('SELECT ($1).%I', key_col) INTO v_key USING NEW;
    ELSIF TG_OP = 'DELETE' THEN
        v_old := to_jsonb(OLD);
        v_new := NULL;
        EXECUTE format('SELECT ($1).%I', key_col) INTO v_key USING OLD;
    END IF;

    INSERT INTO log_islemler(tablo_adi, islem_turu, kayit_id, eski_deger, yeni_deger)
    VALUES (TG_TABLE_NAME, TG_OP, v_key, v_old::text, v_new::text);

    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$_$;


ALTER FUNCTION public.fn_log_islem() OWNER TO teocan;

--
-- Name: fn_recalc_stok(bigint); Type: FUNCTION; Schema: public; Owner: teocan
--

CREATE FUNCTION public.fn_recalc_stok(p_urun_id bigint) RETURNS void
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_alis bigint;
    v_satis bigint;
    v_net bigint;
BEGIN
    SELECT COALESCE(SUM(alinan_adet), 0)
    INTO v_alis
    FROM urun_tedarikci_alis
    WHERE urun_id = p_urun_id
      AND (alis_durumu IS DISTINCT FROM FALSE);

    SELECT COALESCE(SUM(satilan_adet), 0)
    INTO v_satis
    FROM urun_musteri_islem
    WHERE urun_id = p_urun_id;

    v_net := v_alis - v_satis;

    -- Update or insert into urun_stok
    IF EXISTS (SELECT 1 FROM urun_stok WHERE urun_id = p_urun_id) THEN
        UPDATE urun_stok
        SET toplam_stok = v_net
        WHERE urun_id = p_urun_id;
    ELSE
        INSERT INTO urun_stok (urun_id, toplam_stok, referans_degeri)
        VALUES (p_urun_id, v_net, 0);
    END IF;
END;
$$;


ALTER FUNCTION public.fn_recalc_stok(p_urun_id bigint) OWNER TO teocan;

--
-- Name: fn_set_toplam_alis_tutari(); Type: FUNCTION; Schema: public; Owner: teocan
--

CREATE FUNCTION public.fn_set_toplam_alis_tutari() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.alinan_adet IS NULL THEN
        NEW.alinan_adet := 0;
    END IF;
    IF NEW.urun_alis_fiyati IS NULL THEN
        NEW.urun_alis_fiyati := 0;
    END IF;
    NEW.toplam_alis_tutari := NEW.alinan_adet * NEW.urun_alis_fiyati;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.fn_set_toplam_alis_tutari() OWNER TO teocan;

--
-- Name: fn_set_toplam_satis_tutari(); Type: FUNCTION; Schema: public; Owner: teocan
--

CREATE FUNCTION public.fn_set_toplam_satis_tutari() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    IF NEW.satilan_adet IS NULL THEN
        NEW.satilan_adet := 0;
    END IF;
    IF NEW.urun_satis_fiyati IS NULL THEN
        NEW.urun_satis_fiyati := 0;
    END IF;
    NEW.toplam_satis_tutari := NEW.satilan_adet * NEW.urun_satis_fiyati;
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.fn_set_toplam_satis_tutari() OWNER TO teocan;

--
-- Name: fn_update_stok_alis(); Type: FUNCTION; Schema: public; Owner: teocan
--

CREATE FUNCTION public.fn_update_stok_alis() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_urun_id bigint;
BEGIN
    v_urun_id := COALESCE(NEW.urun_id, OLD.urun_id);
    PERFORM fn_recalc_stok(v_urun_id);
    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$$;


ALTER FUNCTION public.fn_update_stok_alis() OWNER TO teocan;

--
-- Name: fn_update_stok_satis(); Type: FUNCTION; Schema: public; Owner: teocan
--

CREATE FUNCTION public.fn_update_stok_satis() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_urun_id bigint;
BEGIN
    v_urun_id := COALESCE(NEW.urun_id, OLD.urun_id);
    PERFORM fn_recalc_stok(v_urun_id);
    IF TG_OP = 'DELETE' THEN
        RETURN OLD;
    ELSE
        RETURN NEW;
    END IF;
END;
$$;


ALTER FUNCTION public.fn_update_stok_satis() OWNER TO teocan;

--
-- Name: log_islemler_log_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.log_islemler_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.log_islemler_log_id_seq OWNER TO teocan;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: log_islemler; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.log_islemler (
    log_id bigint DEFAULT nextval('public.log_islemler_log_id_seq'::regclass) NOT NULL,
    tablo_adi character varying(50) NOT NULL,
    islem_turu character varying(20) NOT NULL,
    kayit_id bigint,
    eski_deger text,
    yeni_deger text,
    islem_zamani timestamp with time zone DEFAULT now()
);


ALTER TABLE public.log_islemler OWNER TO teocan;

--
-- Name: musteri; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.musteri (
    musteri_id bigint NOT NULL,
    musteri_adi character varying(255) NOT NULL,
    musteri_iletisim_no character varying(20),
    musteri_memnuniyeti double precision
);


ALTER TABLE public.musteri OWNER TO teocan;

--
-- Name: musteri_musteri_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.musteri_musteri_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.musteri_musteri_id_seq OWNER TO teocan;

--
-- Name: musteri_musteri_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.musteri_musteri_id_seq OWNED BY public.musteri.musteri_id;


--
-- Name: tedarikci; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.tedarikci (
    tedarikci_id bigint NOT NULL,
    tedarikci_adi character varying(255) NOT NULL,
    tedarikci_iletisim_no character varying(20),
    tedarikci_memnuniyeti double precision
);


ALTER TABLE public.tedarikci OWNER TO teocan;

--
-- Name: tedarikci_tedarikci_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.tedarikci_tedarikci_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.tedarikci_tedarikci_id_seq OWNER TO teocan;

--
-- Name: tedarikci_tedarikci_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.tedarikci_tedarikci_id_seq OWNED BY public.tedarikci.tedarikci_id;


--
-- Name: urun; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.urun (
    urun_id bigint NOT NULL,
    urun_adi character varying(255) NOT NULL,
    birim character varying(50),
    kategori character varying(100)
);


ALTER TABLE public.urun OWNER TO teocan;

--
-- Name: urun_musteri_islem; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.urun_musteri_islem (
    islem_id bigint NOT NULL,
    musteri_id bigint NOT NULL,
    urun_id bigint NOT NULL,
    satilan_adet bigint DEFAULT '0'::bigint,
    urun_satis_fiyati double precision DEFAULT '0'::double precision,
    toplam_satis_tutari double precision DEFAULT '0'::double precision,
    islem_tarihi date DEFAULT CURRENT_DATE,
    musteri_teslimat_tarihi date,
    islemin_durumu character varying(50) DEFAULT 'DEVAM EDIYOR'::character varying
);


ALTER TABLE public.urun_musteri_islem OWNER TO teocan;

--
-- Name: urun_musteri_islem_islem_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.urun_musteri_islem_islem_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.urun_musteri_islem_islem_id_seq OWNER TO teocan;

--
-- Name: urun_musteri_islem_islem_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.urun_musteri_islem_islem_id_seq OWNED BY public.urun_musteri_islem.islem_id;


--
-- Name: urun_stok; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.urun_stok (
    stok_id bigint NOT NULL,
    urun_id bigint NOT NULL,
    toplam_stok bigint DEFAULT '0'::bigint,
    referans_degeri bigint DEFAULT '0'::bigint
);


ALTER TABLE public.urun_stok OWNER TO teocan;

--
-- Name: urun_stok_stok_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.urun_stok_stok_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.urun_stok_stok_id_seq OWNER TO teocan;

--
-- Name: urun_stok_stok_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.urun_stok_stok_id_seq OWNED BY public.urun_stok.stok_id;


--
-- Name: urun_tedarikci_alis; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.urun_tedarikci_alis (
    alis_id bigint NOT NULL,
    tedarikci_id bigint NOT NULL,
    urun_id bigint NOT NULL,
    alinan_adet bigint DEFAULT '0'::bigint,
    urun_alis_fiyati double precision DEFAULT '0'::double precision,
    toplam_alis_tutari double precision DEFAULT '0'::double precision,
    alis_tarihi date DEFAULT CURRENT_DATE,
    tedarikci_teslimat_tarihi date,
    alis_durumu boolean
);


ALTER TABLE public.urun_tedarikci_alis OWNER TO teocan;

--
-- Name: urun_tedarikci_alis_alis_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.urun_tedarikci_alis_alis_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.urun_tedarikci_alis_alis_id_seq OWNER TO teocan;

--
-- Name: urun_tedarikci_alis_alis_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.urun_tedarikci_alis_alis_id_seq OWNED BY public.urun_tedarikci_alis.alis_id;


--
-- Name: urun_urun_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.urun_urun_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.urun_urun_id_seq OWNER TO teocan;

--
-- Name: urun_urun_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.urun_urun_id_seq OWNED BY public.urun.urun_id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: teocan
--

CREATE TABLE public.users (
    users_id bigint NOT NULL,
    name character varying(100) NOT NULL,
    surname character varying(100),
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    role character varying(50) DEFAULT 'personel'::character varying NOT NULL,
    is_approved boolean DEFAULT false,
    created_at timestamp with time zone DEFAULT now()
);


ALTER TABLE public.users OWNER TO teocan;

--
-- Name: users_users_id_seq; Type: SEQUENCE; Schema: public; Owner: teocan
--

CREATE SEQUENCE public.users_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_users_id_seq OWNER TO teocan;

--
-- Name: users_users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: teocan
--

ALTER SEQUENCE public.users_users_id_seq OWNED BY public.users.users_id;


--
-- Name: users_view; Type: VIEW; Schema: public; Owner: teocan
--

CREATE VIEW public.users_view AS
 SELECT users.users_id,
    users.name,
    users.surname,
    users.email,
    users.password,
    users.role,
    users.is_approved,
    users.created_at,
    concat(users.name, ' ', COALESCE(users.surname, ''::character varying)) AS full_name
   FROM public.users;


ALTER TABLE public.users_view OWNER TO teocan;

--
-- Name: musteri musteri_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.musteri ALTER COLUMN musteri_id SET DEFAULT nextval('public.musteri_musteri_id_seq'::regclass);


--
-- Name: tedarikci tedarikci_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.tedarikci ALTER COLUMN tedarikci_id SET DEFAULT nextval('public.tedarikci_tedarikci_id_seq'::regclass);


--
-- Name: urun urun_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun ALTER COLUMN urun_id SET DEFAULT nextval('public.urun_urun_id_seq'::regclass);


--
-- Name: urun_musteri_islem islem_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_musteri_islem ALTER COLUMN islem_id SET DEFAULT nextval('public.urun_musteri_islem_islem_id_seq'::regclass);


--
-- Name: urun_stok stok_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_stok ALTER COLUMN stok_id SET DEFAULT nextval('public.urun_stok_stok_id_seq'::regclass);


--
-- Name: urun_tedarikci_alis alis_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_tedarikci_alis ALTER COLUMN alis_id SET DEFAULT nextval('public.urun_tedarikci_alis_alis_id_seq'::regclass);


--
-- Name: users users_id; Type: DEFAULT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.users ALTER COLUMN users_id SET DEFAULT nextval('public.users_users_id_seq'::regclass);


--
-- Data for Name: log_islemler; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.log_islemler (log_id, tablo_adi, islem_turu, kayit_id, eski_deger, yeni_deger, islem_zamani) FROM stdin;
1	musteri	INSERT	3	\N	{"musteri_id": 3, "musteri_adi": "Log Test Müşteri", "musteri_iletisim_no": null, "musteri_memnuniyeti": null}	2025-11-27 13:34:02.394652+03
2	musteri	UPDATE	1	{"musteri_id": 1, "musteri_adi": "ABC Şirketi", "musteri_iletisim_no": 5551234567, "musteri_memnuniyeti": 4.5}	{"musteri_id": 1, "musteri_adi": "Log Test Müşteri - Güncel", "musteri_iletisim_no": 5551234567, "musteri_memnuniyeti": 4.5}	2025-11-27 13:34:02.401722+03
3	musteri	DELETE	1	{"musteri_id": 1, "musteri_adi": "Log Test Müşteri - Güncel", "musteri_iletisim_no": 5551234567, "musteri_memnuniyeti": 4.5}	\N	2025-11-27 13:34:02.403405+03
\.


--
-- Data for Name: musteri; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.musteri (musteri_id, musteri_adi, musteri_iletisim_no, musteri_memnuniyeti) FROM stdin;
2	XYZ Ltd.	5559876543	4.2
3	Log Test Müşteri	\N	\N
\.


--
-- Data for Name: tedarikci; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.tedarikci (tedarikci_id, tedarikci_adi, tedarikci_iletisim_no, tedarikci_memnuniyeti) FROM stdin;
1	Tedarik AŞ	5551112233	4.8
2	Malzeme Ltd.	5554445566	4
\.


--
-- Data for Name: urun; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.urun (urun_id, urun_adi, birim, kategori) FROM stdin;
1	Nervürlü ve Düz İnşaat Demiri	ton	Demir ve Çelik
2	Nervürlü ve Düz Kangal Demiri	ton	Demir ve Çelik
3	Nervürlü ve Düz Hazır Demiri	ton	Demir ve Çelik
4	Soğuk Çekme Demir	ton	Hasır Çelik
5	Kangal Demir	ton	Hasır Çelik
6	Özel Çelik Hasır	ton	Hasır Çelik
7	Beton Çivisi	koli	Çivi ve Tel
8	İnşaat Çivisi	koli	Çivi ve Tel
9	30'luk BİMS	koli	BİMS
10	19'luk BİMS	koli	BİMS
11	25'lik BİMS	koli	BİMS
12	LENTO	koli	BİMS
13	Kilitli Parke Taşı	koli	BİMS
14	Çim Taşı	koli	BİMS
15	Bordür	koli	BİMS
16	Yağmur Oluğu	koli	BİMS
17	15D	koli	BİMS
18	15G	koli	BİMS
19	30Ç	koli	BİMS
20	30AS	koli	BİMS
21	15U	koli	BİMS
22	Hazır Beton	ton	Hazır Beton
\.


--
-- Data for Name: urun_musteri_islem; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.urun_musteri_islem (islem_id, musteri_id, urun_id, satilan_adet, urun_satis_fiyati, toplam_satis_tutari, islem_tarihi, musteri_teslimat_tarihi, islemin_durumu) FROM stdin;
\.


--
-- Data for Name: urun_stok; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.urun_stok (stok_id, urun_id, toplam_stok, referans_degeri) FROM stdin;
1	1	500	100
2	2	50	100
3	3	200	50
\.


--
-- Data for Name: urun_tedarikci_alis; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.urun_tedarikci_alis (alis_id, tedarikci_id, urun_id, alinan_adet, urun_alis_fiyati, toplam_alis_tutari, alis_tarihi, tedarikci_teslimat_tarihi, alis_durumu) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: teocan
--

COPY public.users (users_id, name, surname, email, password, role, is_approved, created_at) FROM stdin;
1	Yönetici	Admin	yonetici@test.com	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	yonetici	t	2025-11-27 13:19:39.958298+03
2	Personel	Test	personel@test.com	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	personel	t	2025-11-27 13:19:39.958298+03
\.


--
-- Name: log_islemler_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.log_islemler_log_id_seq', 1, false);


--
-- Name: musteri_musteri_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.musteri_musteri_id_seq', 1, false);


--
-- Name: tedarikci_tedarikci_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.tedarikci_tedarikci_id_seq', 1, false);


--
-- Name: urun_musteri_islem_islem_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.urun_musteri_islem_islem_id_seq', 1, false);


--
-- Name: urun_stok_stok_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.urun_stok_stok_id_seq', 1, false);


--
-- Name: urun_tedarikci_alis_alis_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.urun_tedarikci_alis_alis_id_seq', 1, false);


--
-- Name: urun_urun_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.urun_urun_id_seq', 1, false);


--
-- Name: users_users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: teocan
--

SELECT pg_catalog.setval('public.users_users_id_seq', 1, false);


--
-- Name: log_islemler log_islemler_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.log_islemler
    ADD CONSTRAINT log_islemler_pkey PRIMARY KEY (log_id);


--
-- Name: musteri musteri_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.musteri
    ADD CONSTRAINT musteri_pkey PRIMARY KEY (musteri_id);


--
-- Name: tedarikci tedarikci_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.tedarikci
    ADD CONSTRAINT tedarikci_pkey PRIMARY KEY (tedarikci_id);


--
-- Name: urun_musteri_islem urun_musteri_islem_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_musteri_islem
    ADD CONSTRAINT urun_musteri_islem_pkey PRIMARY KEY (islem_id);


--
-- Name: urun urun_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun
    ADD CONSTRAINT urun_pkey PRIMARY KEY (urun_id);


--
-- Name: urun_stok urun_stok_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_stok
    ADD CONSTRAINT urun_stok_pkey PRIMARY KEY (stok_id);


--
-- Name: urun_tedarikci_alis urun_tedarikci_alis_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_tedarikci_alis
    ADD CONSTRAINT urun_tedarikci_alis_pkey PRIMARY KEY (alis_id);


--
-- Name: users users_email_key; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_key UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (users_id);


--
-- Name: idx_musteri_adi; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_musteri_adi ON public.musteri USING btree (musteri_adi);


--
-- Name: idx_musteri_islem_durum; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_musteri_islem_durum ON public.urun_musteri_islem USING btree (islemin_durumu);


--
-- Name: idx_musteri_islem_musteri_id; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_musteri_islem_musteri_id ON public.urun_musteri_islem USING btree (musteri_id);


--
-- Name: idx_musteri_islem_tarih; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_musteri_islem_tarih ON public.urun_musteri_islem USING btree (islem_tarihi);


--
-- Name: idx_musteri_islem_urun_id; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_musteri_islem_urun_id ON public.urun_musteri_islem USING btree (urun_id);


--
-- Name: idx_tedarikci_adi; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_tedarikci_adi ON public.tedarikci USING btree (tedarikci_adi);


--
-- Name: idx_tedarikci_alis_durum; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_tedarikci_alis_durum ON public.urun_tedarikci_alis USING btree (alis_durumu);


--
-- Name: idx_tedarikci_alis_tarih; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_tedarikci_alis_tarih ON public.urun_tedarikci_alis USING btree (alis_tarihi);


--
-- Name: idx_tedarikci_alis_tedarikci_id; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_tedarikci_alis_tedarikci_id ON public.urun_tedarikci_alis USING btree (tedarikci_id);


--
-- Name: idx_tedarikci_alis_urun_id; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_tedarikci_alis_urun_id ON public.urun_tedarikci_alis USING btree (urun_id);


--
-- Name: idx_urun_adi; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_urun_adi ON public.urun USING btree (urun_adi);


--
-- Name: idx_urun_kategori; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_urun_kategori ON public.urun USING btree (kategori);


--
-- Name: idx_urun_stok_kritik; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_urun_stok_kritik ON public.urun_stok USING btree (toplam_stok, referans_degeri);


--
-- Name: idx_urun_stok_urun_id; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_urun_stok_urun_id ON public.urun_stok USING btree (urun_id);


--
-- Name: idx_users_email; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_users_email ON public.users USING btree (email);


--
-- Name: idx_users_role; Type: INDEX; Schema: public; Owner: teocan
--

CREATE INDEX idx_users_role ON public.users USING btree (role);


--
-- Name: urun_tedarikci_alis trg_log_alis; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_alis AFTER INSERT OR DELETE OR UPDATE ON public.urun_tedarikci_alis FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('alis_id');


--
-- Name: musteri trg_log_musteri; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_musteri AFTER INSERT OR DELETE OR UPDATE ON public.musteri FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('musteri_id');


--
-- Name: urun_musteri_islem trg_log_satis; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_satis AFTER INSERT OR DELETE OR UPDATE ON public.urun_musteri_islem FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('islem_id');


--
-- Name: urun_stok trg_log_stok; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_stok AFTER INSERT OR DELETE OR UPDATE ON public.urun_stok FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('stok_id');


--
-- Name: tedarikci trg_log_tedarikci; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_tedarikci AFTER INSERT OR DELETE OR UPDATE ON public.tedarikci FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('tedarikci_id');


--
-- Name: urun trg_log_urun; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_urun AFTER INSERT OR DELETE OR UPDATE ON public.urun FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('urun_id');


--
-- Name: users trg_log_users; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_log_users AFTER INSERT OR DELETE OR UPDATE ON public.users FOR EACH ROW EXECUTE FUNCTION public.fn_log_islem('users_id');


--
-- Name: urun_tedarikci_alis trg_set_toplam_alis_tutari; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_set_toplam_alis_tutari BEFORE INSERT OR UPDATE ON public.urun_tedarikci_alis FOR EACH ROW EXECUTE FUNCTION public.fn_set_toplam_alis_tutari();


--
-- Name: urun_musteri_islem trg_set_toplam_satis_tutari; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_set_toplam_satis_tutari BEFORE INSERT OR UPDATE ON public.urun_musteri_islem FOR EACH ROW EXECUTE FUNCTION public.fn_set_toplam_satis_tutari();


--
-- Name: urun_tedarikci_alis trg_update_stok_alis; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_update_stok_alis AFTER INSERT OR DELETE OR UPDATE ON public.urun_tedarikci_alis FOR EACH ROW EXECUTE FUNCTION public.fn_update_stok_alis();


--
-- Name: urun_musteri_islem trg_update_stok_satis; Type: TRIGGER; Schema: public; Owner: teocan
--

CREATE TRIGGER trg_update_stok_satis AFTER INSERT OR DELETE OR UPDATE ON public.urun_musteri_islem FOR EACH ROW EXECUTE FUNCTION public.fn_update_stok_satis();


--
-- Name: urun_musteri_islem urun_musteri_islem_musteri_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_musteri_islem
    ADD CONSTRAINT urun_musteri_islem_musteri_id_fkey FOREIGN KEY (musteri_id) REFERENCES public.musteri(musteri_id) ON DELETE CASCADE;


--
-- Name: urun_musteri_islem urun_musteri_islem_urun_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_musteri_islem
    ADD CONSTRAINT urun_musteri_islem_urun_id_fkey FOREIGN KEY (urun_id) REFERENCES public.urun(urun_id) ON DELETE CASCADE;


--
-- Name: urun_stok urun_stok_urun_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_stok
    ADD CONSTRAINT urun_stok_urun_id_fkey FOREIGN KEY (urun_id) REFERENCES public.urun(urun_id) ON DELETE CASCADE;


--
-- Name: urun_tedarikci_alis urun_tedarikci_alis_tedarikci_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_tedarikci_alis
    ADD CONSTRAINT urun_tedarikci_alis_tedarikci_id_fkey FOREIGN KEY (tedarikci_id) REFERENCES public.tedarikci(tedarikci_id) ON DELETE CASCADE;


--
-- Name: urun_tedarikci_alis urun_tedarikci_alis_urun_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: teocan
--

ALTER TABLE ONLY public.urun_tedarikci_alis
    ADD CONSTRAINT urun_tedarikci_alis_urun_id_fkey FOREIGN KEY (urun_id) REFERENCES public.urun(urun_id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict DjaFknSi0i4J1VBxOa07IbaeeM574fbqv3YzoRi2Ys83mO8gMjw9Uuf8byoTeJA

