# Задание 1
select date(time_created), hour(time_created), object_id, object, sum(amount)
from analytics
where (object = 'boosterpack' and action = 'sub') or  (object = 'like' and action = 'add')
  and time_created > DATE_SUB(NOW(), INTERVAL 30 DAY)
group by date(time_created), hour(time_created), object_id
order by date(time_created), hour(time_created), object_id



#  Задание 2
select user_id, object, sum(amount)
from analytics
where user_id = 2
  and object in ('wallet', 'like') and action='add'
group by object

