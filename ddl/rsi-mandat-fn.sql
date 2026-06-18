-- ============================================================
-- FUNCTIONS
-- ============================================================

-- Search campaigns with filters
CREATE OR REPLACE FUNCTION fn_search_campaigns(
    p_keyword VARCHAR DEFAULT NULL,
    p_kategori INT DEFAULT NULL,
    p_kode_wilayah VARCHAR DEFAULT NULL,
    p_status VARCHAR DEFAULT NULL,
    p_target_audiens VARCHAR DEFAULT NULL,
    p_order_by VARCHAR DEFAULT 'terbaru'
)
RETURNS SETOF v_campaign_public_detail
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT * FROM v_campaign_public_detail v
    WHERE
        (p_keyword IS NULL OR v.judul ILIKE '%' || p_keyword || '%') AND
        (p_kategori IS NULL OR EXISTS (SELECT 1 FROM campaign c WHERE c.id_campaign = v.id_campaign AND c.id_kategori = p_kategori)) AND
        (p_kode_wilayah IS NULL OR EXISTS (SELECT 1 FROM campaign c WHERE c.id_campaign = v.id_campaign AND c.kode_wilayah LIKE p_kode_wilayah || '%')) AND
        (p_status IS NULL OR v.status = p_status) AND
        (p_target_audiens IS NULL OR v.target_audiens = p_target_audiens)
    ORDER BY
        CASE WHEN p_order_by = 'terpopuler' THEN v.jumlah_donatur END DESC NULLS LAST,
        CASE WHEN p_order_by = 'hampir_selesai' THEN v.tanggal_selesai END ASC NULLS LAST,
        CASE WHEN p_order_by = 'terbaru' THEN v.tanggal_mulai END DESC NULLS LAST,
        v.id_campaign DESC;
END;
$$;

-- Get donation chart data
CREATE OR REPLACE FUNCTION fn_get_donation_chart(
    p_id_komunitas INT,
    p_id_campaign INT DEFAULT NULL,
    p_start_date DATE DEFAULT (CURRENT_DATE - INTERVAL '30 days'),
    p_end_date DATE DEFAULT CURRENT_DATE
)
RETURNS TABLE (tanggal DATE, total_donasi BIGINT, jumlah_donatur BIGINT)
LANGUAGE plpgsql
AS $$
BEGIN
    RETURN QUERY
    SELECT
        DATE(d.created_at) AS tanggal,
        SUM(d.nominal) AS total_donasi,
        COUNT(DISTINCT d.id_user) AS jumlah_donatur
    FROM donasi d
    JOIN campaign c ON d.id_campaign = c.id_campaign
    WHERE
        c.id_komunitas = p_id_komunitas AND
        d.status_pembayaran = 'berhasil' AND
        (p_id_campaign IS NULL OR c.id_campaign = p_id_campaign) AND
        DATE(d.created_at) BETWEEN p_start_date AND p_end_date
    GROUP BY DATE(d.created_at)
    ORDER BY tanggal ASC;
END;
$$;