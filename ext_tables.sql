CREATE TABLE tx_webvitalstracker_measure
(
    uuid         varchar(36)           NOT NULL,
    page_id      int(11)               NOT NULL,
    sys_language int(11)               NOT NULL,
    date         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    cls          float     DEFAULT NULL,
    counter_cls  int(11)   DEFAULT '0' NOT NULL,
    fcp          float     DEFAULT NULL,
    counter_fcp  int(11)   DEFAULT '0' NOT NULL,
    fid          float     DEFAULT NULL,
    counter_fid  int(11)   DEFAULT '0' NOT NULL,
    lcp          float     DEFAULT NULL,
    counter_lcp  int(11)   DEFAULT '0' NOT NULL,
    ttfb         float     DEFAULT NULL,
    counter_ttfb int(11)   DEFAULT '0' NOT NULL,

    UNIQUE request (uuid, page_id, sys_language),
    KEY page (page_id)
);
