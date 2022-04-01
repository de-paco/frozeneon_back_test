 -- Запрос 1. Показать, сколько получили юзеры из каждого бустерпака не допер как :) Не смог подружить join и group by
SELECT HOUR(`a`.`time_created`) as `hour`,
    `a`.`object_id`,
    SUM(`a`.`amount`) as `user_spent`
FROM `analytics` `a`
WHERE `a`.`object` = 'boosterpack'
  AND `a`.`time_created`
    > NOW() - INTERVAL 30 DAY
GROUP BY `hour`,
    `a`.`object_id`

-- Запрос 2. Та же проблема, не вышло