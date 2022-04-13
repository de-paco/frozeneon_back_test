SELECT
    object_id,
    SUM(amount) as received,
    SUM(price) as spent,
    DATE_FORMAT(analytics.time_created, "%Y-%m-%d %H") as hour
FROM `analytics`, `boosterpack`
WHERE object_id = boosterpack.id AND action = 'buy'
GROUP BY object_id, hour ORDER BY hour, object_id;

SELECT personaname,
       AVG(likes_balance),
       AVG(wallet_balance),
       AVG(wallet_total_refilled),
       SUM(IF(amount is NULL, 0, amount)) as received_likes
FROM `user` LEFT JOIN analytics ON user.id = user_id
GROUP BY user.id;