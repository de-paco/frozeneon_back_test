-- Сколько денег потрачено на бустерпаки по каждому паку отдельно, почасовая выборка.
-- Также нужно показать, сколько получили юзеры из каждого пока в эквиваленте $.
-- Выборка должна быть за последние 30 дней.

SELECT 
	`t1`.`date` AS `date`,
	`t1`.`time` AS `time`,
	`t1`.`boosterpack` AS `boosterpack`,
	SUM(`t1`.`amount`) AS `money_withdrawn`,
	SUM(`t2`.`amount`) AS `likes_refilled`

FROM
(
	SELECT
		`id`,
		`object_id` AS `boosterpack`,
		DATE(`time_created`) AS `date`,
		HOUR(`time_created`) AS `time`,
		`amount`
	FROM 
		`analytics`
	WHERE
		`object` = 'boosterpack' AND
		`action` = 'withdrawn' AND
		DATE(`time_created`) BETWEEN DATE(NOW()) - 30 AND DATE(NOW())
) t1

RIGHT JOIN
(
	SELECT
		`object_id` AS `parent`,
		`amount`
	FROM 
		`analytics`
	WHERE
		`object` = 'likes' AND
		`action` = 'refilled'
) t2

ON
	t1.id = t2.parent

GROUP BY
	`t1`.`boosterpack`,
	`t1`.`date`,
	`t1`.`time`

ORDER BY
	`date` DESC,
	`time` DESC,
	`boosterpack`
;





-- Выборка по юзеру, на сколько он пополнил баланс и сколько получил лайков за все время.
-- Текущий остаток баланса в $ и лайков на счету.

SET @user = 1;

SELECT
	`id`,
	`personaname` AS `username`,
	`wallet_total_refilled` AS `wallet_refilled`,
	(
		SELECT
			SUM(`amount`)
		FROM
			`analytics`
		WHERE
			`user_id` = @user AND
			`object` = 'likes' AND
			`action` = 'refilled'
	) AS `likes_refilled`,
	`wallet_balance`,
	`likes_balance`

FROM
	`user`

WHERE
	`id` = @user
;
