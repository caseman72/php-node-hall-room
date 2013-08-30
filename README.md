php-node-hall-room
==================

In order to work you need to set 3 env variables:
HALL_EMAIL
HALL_PASSWORD
BEACON_ROOMID


Note: the post-to-hall will work with any room_id and file:
node post-to-hall.js room_id file_path

Note: it parses lines that start with '#-' - this is to allow for comments and numbered lists


Note: hall-client should work via:
npm install hall-client

Note: the included version removes the extra console.log statements so it is quieter





