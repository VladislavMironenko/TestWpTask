1. Для начала заберем проект к себе на машину:
git clone https://github.com/VladislavMironenko/BotTestTask.git

2. После этого нужно перейти в папку куда вы выгрузили проект и сделать:
 - docker-compose up --build (Важный момент - при использование этой команды у вас должен быть открыт Docker Desktop - https://www.docker.com/products/docker-desktop/)

3. После этого нужно немного подождать для подгрузки всех библиотек , и вы сможете взаимодействовать с wp 
 - на главной странице будет плагин , в редакторе страниц , чтоб добавлять плагин нужна использовать shortcode - ( [wp_chat] )
 - чтоб зайти в админ панель http://localhost:8000/wp-login.php ( доступы : login - admin , pass - testTask )

