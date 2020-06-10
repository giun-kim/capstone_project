# Yeungjin University capston design
* Team name : Infinite
* Project name : baribari
* Department : computer information
* Major : Web database

## Introduction to the Project
Currently, most of the deliveries are delivered directly to the campus and government offices by motorcycle or walking. This can cause motorcycle accidents and it takes time to deliver them directly. 
I wish I could use this system to reduce accidents and reduce the time it takes to deliver goods. So we planned this project.

## Project Purpose
This project purposes to develop an autonomous driving delivery system within a limited area, such as campus, government offices, etc.

## project characteristic
* Deliver the goods you wish to deliver via rc car.
* User focused, convenient app screen configuration.
* The administrator directly manages stops and routes in a flexible manner.
* Identifying traffic through statistical graphs of delivery information
* View current delivery status and rc car status at a glance

## How to use  
- production environment: Laravel Framework 7.10.2 & PHP 7.4.6
- composer install
- npm install
## skills of use
* Web : Vue.js
* App : Native App
* Server : Node.js, Laravel

## Part
My part is back end and i used Laravel
* Server -> Web
  * Control page
    * Displays all of the contents of the control page when it is first loaded.
    * Information sent on first load.
    * Present to state of operation, Present to delivery status, Standby Cancellation Status, Last week's Calling Building Rank, Current Average Latency, Map - station location and name, RC car location and name,
    * When you click the rc car marker on the map, the status and delivery information of the rc car are sent.
  * Statistics page
    * This page is a statistics page where you can view the number of deliveries, the number of queues, the number of cancellations, and the average wait time.
    * You can view it by specifying the cumulative, average, and date.
  * Management page
    * You can register, modify and delete for RC cars, check points, station on this page.
    * When registering a path, a total of two paths are registered up to the path that reversed the path.

* Server -> App
  * Main page
    * Send delivery information of the log-in user when load the main activity
  * Call to delivery
    * click to call button and all station information will be sent.
    * click to two station buttons to send the route between the stations.
    * When the receiver name is same, a list of the same person is sent to verify the same name.
  * Delivery history
    * You can check the Completed delivery.
    * You can select the desired date to view information about the delivery you sent or received on that date.
