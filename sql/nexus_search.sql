DELIMITER //

DROP PROCEDURE IF EXISTS nexus_search //

CREATE PROCEDURE nexus_search(IN search_term VARCHAR(255))
BEGIN
    SET @pattern = CONCAT('%', search_term, '%');
    SET @c_size = 10; -- Valeur fixe de contexte

    -- Recherche dans les commandes
    SELECT 
        o.name AS object_name,
        b.name AS eqLogic_name, 
        NULL AS scenario_name, 
        a.name AS cmd_name, 
        a.id AS cmd_id,
        CONCAT('...', SUBSTRING(a.configuration, GREATEST(1, LOCATE(search_term, a.configuration) - @c_size), (@c_size * 2) + LENGTH(search_term)), '...') AS context
    FROM cmd AS a 
    JOIN eqLogic AS b ON a.eqLogic_id = b.id
    LEFT JOIN object o ON b.object_id = o.id
    WHERE a.configuration LIKE @pattern

    UNION ALL

    -- Recherche dans les scénarios
    SELECT 
        o.name AS object_name,
        NULL AS eqLogic_name, 
        s.name AS scenario_name, 
        se.type AS cmd_name, 
        se.id AS cmd_id,
        CONCAT('...', SUBSTRING(se.expression, GREATEST(1, LOCATE(search_term, se.expression) - @c_size), (@c_size * 2) + LENGTH(search_term)), '...') AS context
    FROM scenarioExpression se
    JOIN scenarioSubElement sse ON se.scenarioSubElement_id = sse.id
    JOIN scenarioElement el ON sse.scenarioElement_id = el.id
    JOIN scenario s ON s.scenarioElement LIKE CONCAT('%"', el.id, '"%')
    LEFT JOIN object o ON s.object_id = o.id
    WHERE se.expression LIKE @pattern;
END //

DELIMITER ;
