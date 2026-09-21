/*
SQLyog Community v13.3.0 (64 bit)
MySQL - 10.4.32-MariaDB : Database - dhoti_mahal
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`dhoti_mahal` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `dhoti_mahal`;

/*Table structure for table `admins` */

DROP TABLE IF EXISTS `admins`;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `admins` */

insert  into `admins`(`id`,`name`,`email`,`password`,`is_active`,`created_at`) values 
(1,'Admin','admin@dhotimahal.com','$2y$10$TmPKo1MLMhQmCi/X4iRTFeclQvYknrUFQT6idhEvNbgWKWsMKRo2C',1,'2026-03-27 09:45:38');

/*Table structure for table `banners` */

DROP TABLE IF EXISTS `banners`;

CREATE TABLE `banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `subtitle` varchar(300) DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `button_text` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `banners` */

insert  into `banners`(`id`,`title`,`subtitle`,`image`,`link`,`button_text`,`is_active`,`sort_order`,`created_at`) values 
(5,'','','banner_6a7d4d690adce.png','','',1,0,'2026-08-13 10:21:53');

/*Table structure for table `cart` */

DROP TABLE IF EXISTS `cart`;

CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `size` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cart` (`user_id`,`session_id`,`product_id`,`size`),
  KEY `idx_cart_user_session` (`user_id`,`session_id`),
  KEY `fk_cart_product` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=121 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `cart` */

insert  into `cart`(`id`,`user_id`,`session_id`,`product_id`,`quantity`,`size`,`created_at`,`updated_at`) values 
(115,3,NULL,3,8,'','2026-05-17 15:15:54','2026-05-17 15:16:04'),
(116,3,NULL,9,1,'','2026-05-17 15:16:45','2026-05-17 15:44:16'),
(117,3,NULL,2,6,'','2026-05-17 15:17:39','2026-05-17 15:17:39'),
(120,2,NULL,9,6,'4 Meters','2026-08-21 12:18:45','2026-08-21 12:19:20');

/*Table structure for table `categories` */

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `categories` */

insert  into `categories`(`id`,`name`,`slug`,`description`,`image`,`is_active`,`sort_order`,`created_at`) values 
(1,'Traditional Dhotis','traditional-dhotis','Classic handwoven traditional dhotis for all occasions',NULL,1,1,'2026-03-27 09:45:38'),
(2,'Silk Dhotis','silk-dhotis','Premium pure silk dhotis for weddings and festivals',NULL,0,2,'2026-03-27 09:45:38'),
(3,'Cotton Dhotis','cotton-dhotis','Comfortable everyday cotton dhotis',NULL,1,3,'2026-03-27 09:45:38'),
(4,'Designer Dhotis','designer-dhotis','Modern designer dhotis with contemporary patterns',NULL,1,4,'2026-03-27 09:45:38'),
(5,'Kurta Sets','kurta-sets','Matching kurta and dhoti sets for complete look',NULL,1,5,'2026-03-27 09:45:38'),
(6,'Accessories','accessories','Angavastram, gamchas and other accessories',NULL,1,6,'2026-03-27 09:45:38');

/*Table structure for table `order_items` */

DROP TABLE IF EXISTS `order_items`;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(200) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_items_order_id` (`order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_item_quantity_positive` CHECK (`quantity` > 0),
  CONSTRAINT `chk_item_total_positive` CHECK (`total` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `order_items` */

insert  into `order_items`(`id`,`order_id`,`product_id`,`product_name`,`product_image`,`size`,`price`,`quantity`,`total`) values 
(1,1,6,'Royal Wedding Dhoti Kurta Set',NULL,'',8999.00,1,8999.00),
(2,1,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',2499.00,1,2499.00),
(3,2,1,'Kanjivaram Pure Silk Dhoti',NULL,'',3499.00,11,38489.00),
(4,2,3,'Bengal Handloom Dhoti',NULL,'',899.00,8,7192.00),
(5,2,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',2499.00,6,14994.00),
(6,3,1,'Kanjivaram Pure Silk Dhoti',NULL,'',3499.00,6,20994.00),
(7,3,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',2499.00,6,14994.00),
(8,4,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',2499.00,1,2499.00),
(9,5,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','4.5 Meters',2499.00,4,9996.00),
(10,6,5,'Embroidered Designer Dhoti',NULL,'',2199.00,1,2199.00),
(11,7,3,'Bengal Handloom Dhoti',NULL,'5 Meters',899.00,6,5394.00),
(12,7,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','4.5 Meters',2499.00,3,7497.00),
(13,11,2,'Banarasi Silk Dhoti',NULL,'5 Meters',4200.00,3,12600.00),
(14,12,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','4.5 Meters',399.00,1,399.00),
(15,13,4,'Kerala Kasavu Dhoti',NULL,'4 Meters',1299.00,4,5196.00),
(16,14,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','4.5 Meters',2499.00,4,9996.00),
(17,14,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','4.5 Meters',399.00,3,1197.00),
(18,15,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,1,399.00),
(19,16,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,1,399.00),
(20,17,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,1,399.00),
(21,18,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,2,798.00),
(22,19,6,'Royal Wedding Dhoti Kurta Set',NULL,'L',8999.00,1,8999.00),
(23,20,2,'Banarasi Silk Dhoti',NULL,'',4200.00,1,4200.00),
(24,21,5,'Embroidered Designer Dhoti',NULL,'',2199.00,1,2199.00),
(25,22,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',2499.00,1,2499.00),
(26,23,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,1,0.99),
(27,24,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,1,0.99),
(28,25,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,1,0.99),
(29,26,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,1,0.99),
(30,27,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','4.5 Meters',0.99,3,2.97),
(31,28,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','4.5 Meters',0.99,2,1.98),
(32,29,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(33,30,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,1,0.99),
(34,31,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(35,32,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(36,33,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(37,34,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(38,35,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(39,36,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(40,37,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(41,38,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(42,39,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(43,40,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,2,1.98),
(44,41,3,'Bengal Handloom Dhoti',NULL,'',899.00,2,1798.00),
(45,42,2,'Banarasi Silk Dhoti',NULL,'',4200.00,1,4200.00),
(46,43,9,'Lucknowi Chikankari Dhoti','prod_69c6453ba0c8f.jpg','',0.99,4,3.96),
(47,44,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,1,399.00),
(48,45,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(49,46,2,'Banarasi Silk Dhoti',NULL,'',4200.00,8,33600.00),
(50,47,3,'Bengal Handloom Dhoti',NULL,'',899.00,3,2697.00),
(51,48,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(52,49,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(53,50,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(54,51,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(55,52,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(56,53,3,'Bengal Handloom Dhoti',NULL,'',899.00,20,17980.00),
(57,54,2,'Banarasi Silk Dhoti',NULL,'',4200.00,1,4200.00),
(58,55,5,'Embroidered Designer Dhoti',NULL,'',2199.00,1,2199.00),
(59,56,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(60,57,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,1,399.00),
(61,58,2,'Banarasi Silk Dhoti',NULL,'',4200.00,1,4200.00),
(62,59,2,'Banarasi Silk Dhoti',NULL,'',4200.00,1,4200.00),
(63,60,7,'Soft Cotton Daily Dhoti','prod_69c7e0afe3970.png','',399.00,3,1197.00),
(64,61,2,'Banarasi Silk Dhoti',NULL,'',4200.00,1,4200.00),
(65,62,5,'Embroidered Designer Dhoti',NULL,'',2199.00,1,2199.00),
(66,63,5,'Embroidered Designer Dhoti',NULL,'',2199.00,1,2199.00),
(67,64,5,'Embroidered Designer Dhoti',NULL,'',2199.00,1,2199.00),
(68,65,3,'Bengal Handloom Dhoti',NULL,'',899.00,1,899.00),
(69,66,3,'Bengal Handloom Dhoti',NULL,'4.5 Meters',899.00,2,1798.00),
(70,67,3,'Bengal Handloom Dhoti',NULL,'',899.00,2,1798.00);

/*Table structure for table `order_tracking` */

DROP TABLE IF EXISTS `order_tracking`;

CREATE TABLE `order_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `status` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_order_tracking_order_id` (`order_id`),
  CONSTRAINT `fk_order_tracking_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=88 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `order_tracking` */

insert  into `order_tracking`(`id`,`order_id`,`status`,`message`,`created_at`) values 
(1,1,'Order Placed','Your order has been placed successfully.','2026-03-28 07:27:45'),
(2,1,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-03-28 07:28:47'),
(3,2,'Order Placed','Your order has been placed successfully.','2026-03-28 19:30:50'),
(4,2,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-03-28 19:31:03'),
(5,3,'Order Placed','Your order has been placed successfully.','2026-03-31 08:41:27'),
(6,3,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-03-31 08:41:37'),
(7,4,'Order Placed','Your order has been placed successfully.','2026-03-31 08:58:41'),
(8,4,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-03-31 08:58:48'),
(9,5,'Order Placed','Your order has been placed successfully.','2026-03-31 09:25:19'),
(10,5,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-03-31 09:25:28'),
(11,6,'Order Placed','Your order has been placed successfully.','2026-03-31 16:53:31'),
(12,7,'Order Placed','Your order has been placed successfully.','2026-04-09 08:01:13'),
(13,7,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-04-09 08:01:23'),
(14,11,'Order Placed','Your order has been placed successfully.','2026-04-20 09:03:30'),
(15,12,'Order Placed','Your order has been placed successfully.','2026-04-20 09:40:30'),
(16,13,'Order Placed','Your order has been placed successfully.','2026-04-20 09:41:11'),
(17,13,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-04-20 09:41:23'),
(18,14,'Order Placed','Your order has been placed successfully.','2026-04-20 09:42:27'),
(19,14,'Payment Confirmed','Payment verified. Your order is confirmed.','2026-04-20 09:42:35'),
(20,15,'Order Placed','Your order has been placed successfully.','2026-04-20 09:49:22'),
(21,16,'Order Placed','Your order has been placed successfully.','2026-04-20 09:50:21'),
(22,17,'Order Placed','Your order has been placed successfully.','2026-04-20 09:54:19'),
(23,18,'Order Placed','Your order has been placed successfully.','2026-04-20 09:56:06'),
(24,19,'Order Placed','Your order has been placed successfully.','2026-04-20 10:04:40'),
(25,20,'Order Placed','Your order has been placed successfully.','2026-04-26 10:27:03'),
(26,21,'Order Placed','Your order has been placed successfully.','2026-05-01 07:10:30'),
(27,22,'Order Placed','Your order has been placed successfully.','2026-05-02 10:55:59'),
(28,23,'Order Placed','Your order has been placed successfully.','2026-05-02 10:59:07'),
(29,24,'Order Placed','Your order has been placed successfully.','2026-05-02 11:00:30'),
(30,25,'Order Placed','Your order has been placed successfully.','2026-05-02 11:08:19'),
(31,26,'Order Placed','Your order has been placed successfully.','2026-05-02 11:12:16'),
(32,27,'Order Placed','Your order has been placed successfully.','2026-05-02 11:12:43'),
(33,28,'Order Placed','Your order has been placed successfully.','2026-05-02 11:13:08'),
(34,29,'Order Placed','Your order has been placed successfully.','2026-05-03 05:48:32'),
(35,30,'Order Placed','Your order has been placed successfully.','2026-05-07 22:17:05'),
(36,31,'Order Placed','Your order has been placed successfully.','2026-05-07 22:17:29'),
(37,32,'Order Placed','Your order has been placed successfully.','2026-05-07 22:49:44'),
(38,33,'Order Placed','Your order has been placed successfully.','2026-05-07 23:02:29'),
(39,34,'Order Placed','Your order has been placed successfully.','2026-05-08 07:44:27'),
(40,35,'Order Placed','Your order has been placed successfully.','2026-05-08 07:44:48'),
(41,36,'Order Placed','Your order has been placed successfully.','2026-05-08 07:48:10'),
(42,37,'Order Placed','Your order has been placed successfully.','2026-05-08 08:04:10'),
(43,38,'Order Placed','Your order has been placed successfully.','2026-05-08 08:07:39'),
(44,39,'Order Placed','Your order has been placed successfully.','2026-05-08 08:15:46'),
(45,39,'Payment Confirmed','Payment verified via Razorpay API','2026-05-08 08:34:44'),
(46,40,'Order Placed','Your order has been placed successfully.','2026-05-08 08:37:07'),
(47,40,'Payment Confirmed','Payment verified via Razorpay API','2026-05-08 08:44:48'),
(48,41,'Order Placed','Your order has been placed successfully.','2026-05-10 09:54:08'),
(49,42,'Order Placed','Your order has been placed successfully.','2026-05-10 09:54:35'),
(50,42,'Payment Confirmed','Payment verified via Razorpay API','2026-05-10 09:58:45'),
(51,43,'Order Placed','Your order has been placed successfully.','2026-05-11 21:37:49'),
(52,44,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-11 21:58:37'),
(53,45,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-11 21:59:21'),
(54,46,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-13 22:20:35'),
(55,47,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-13 22:51:13'),
(56,47,'Payment Confirmed','Payment verified via Razorpay API','2026-05-13 22:52:04'),
(57,48,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-13 22:52:57'),
(58,48,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-13 22:53:55'),
(59,49,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-13 23:06:12'),
(60,49,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-13 23:07:00'),
(61,50,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-14 08:15:46'),
(62,50,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-14 08:17:18'),
(63,51,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-14 08:43:43'),
(64,51,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-14 08:44:23'),
(65,6,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-14 09:14:27'),
(66,52,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-14 09:19:04'),
(67,52,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-14 09:19:47'),
(68,53,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-14 09:22:41'),
(69,54,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-15 09:36:29'),
(70,54,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-15 09:37:35'),
(71,55,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-15 09:37:55'),
(72,56,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-15 09:39:48'),
(73,56,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-15 09:40:27'),
(74,55,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-15 10:01:36'),
(75,57,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-15 10:02:02'),
(76,57,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-15 12:44:23'),
(77,58,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:31:16'),
(78,59,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:31:39'),
(79,60,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:32:37'),
(80,61,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:34:14'),
(81,61,'Payment Confirmed','Payment verified via Razorpay API. Inventory updated.','2026-05-17 14:35:16'),
(82,62,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:49:32'),
(83,63,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:50:51'),
(84,64,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-17 14:52:43'),
(85,65,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-05-18 06:27:47'),
(86,66,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-08-21 12:20:13'),
(87,67,'Order Placed','Your order has been placed successfully. Awaiting payment confirmation.','2026-08-21 12:20:32');

/*Table structure for table `orders` */

DROP TABLE IF EXISTS `orders`;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_email` varchar(150) DEFAULT NULL,
  `guest_phone` varchar(15) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `shipping_city` varchar(100) NOT NULL,
  `shipping_state` varchar(100) NOT NULL,
  `shipping_pincode` varchar(10) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'upi',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `payment_ref` varchar(200) DEFAULT NULL,
  `order_status` enum('placed','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'placed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `razorpay_signature` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  UNIQUE KEY `uk_order_number` (`order_number`),
  KEY `idx_orders_payment_status` (`payment_status`),
  KEY `idx_orders_order_status` (`order_status`),
  KEY `idx_orders_user_id` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_order_total_positive` CHECK (`total` > 0)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `orders` */

insert  into `orders`(`id`,`order_number`,`user_id`,`guest_name`,`guest_email`,`guest_phone`,`shipping_address`,`shipping_city`,`shipping_state`,`shipping_pincode`,`subtotal`,`shipping_cost`,`discount`,`total`,`payment_method`,`payment_status`,`payment_ref`,`order_status`,`notes`,`created_at`,`updated_at`,`razorpay_order_id`,`razorpay_payment_id`,`razorpay_signature`) values 
(1,'DM93BA4426',NULL,'user','user@gmail.com','9808978767','vill-uservillege,west bengal','kolkata','West Bengal','987067',11498.00,0.00,0.00,11498.00,'upi','paid','kmkm','confirmed','','2026-03-28 07:27:45','2026-03-28 07:28:47',NULL,NULL,NULL),
(2,'DM2673ED85',2,'user','user@gmail.com','9345335123','nbh','kj','West Bengal','876543',60675.00,0.00,0.00,60675.00,'upi','refunded','hbj','cancelled','um k','2026-03-28 19:30:50','2026-03-31 08:54:54',NULL,NULL,NULL),
(3,'DMFD1F5986',2,'user','user@gmail.com','8765432198','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','West Bengal','712403',35988.00,0.00,0.00,35988.00,'upi','failed','7fygb','delivered','','2026-03-31 08:41:27','2026-03-31 08:54:32',NULL,NULL,NULL),
(4,'DM9DC65E64',2,'user','user@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','West Bengal','712403',2499.00,0.00,0.00,2499.00,'upi','paid','78','confirmed','','2026-03-31 08:58:41','2026-03-31 08:58:48',NULL,NULL,NULL),
(5,'DM77673E98',2,'user','user@gmail.com','9749724552','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','West Bengal','712403',9996.00,0.00,0.00,9996.00,'upi','paid','fff','confirmed','','2026-03-31 09:25:19','2026-03-31 09:25:28',NULL,NULL,NULL),
(6,'DM36C75E98',2,'user','user@gmail.com','9749724552','bh','bhj','WEST BENGAL','987654',2199.00,0.00,0.00,2199.00,'upi','paid','pay_Sp6H5OFYOEVPEX','confirmed','','2026-03-31 16:53:31','2026-05-14 09:14:27','order_Sp6GnsytasfbjK','pay_Sp6H5OFYOEVPEX','1bef60cdae0120165ec7fad96eb84bc3641016596f8f24d7213dab26bc8b538e'),
(7,'DM11690C72',NULL,'adb','ubar@gmail.com','9234567890','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',12891.00,0.00,0.00,12891.00,'upi','paid','7437jdf','shipped','','2026-04-09 08:01:13','2026-04-09 08:08:07',NULL,NULL,NULL),
(11,'DMACDB1A83',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',12600.00,0.00,0.00,12600.00,'upi','pending',NULL,'placed','','2026-04-20 09:03:30','2026-04-20 09:03:30',NULL,NULL,NULL),
(12,'DM6E874162',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',399.00,99.00,0.00,498.00,'upi','pending',NULL,'placed','','2026-04-20 09:40:30','2026-04-20 09:40:30',NULL,NULL,NULL),
(13,'DMF9D03041',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',5196.00,0.00,0.00,5196.00,'upi','paid','7437jdf','confirmed','','2026-04-20 09:41:11','2026-04-20 09:41:23',NULL,NULL,NULL),
(14,'DMBECD8D35',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',11193.00,0.00,0.00,11193.00,'upi','paid','kmkm','confirmed','','2026-04-20 09:42:27','2026-04-20 09:42:35',NULL,NULL,NULL),
(15,'DMA35A9E91',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9749714552','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',399.00,99.00,0.00,498.00,'upi','pending',NULL,'placed','','2026-04-20 09:49:22','2026-04-20 09:49:22',NULL,NULL,NULL),
(16,'DM547DD378',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',399.00,99.00,0.00,498.00,'upi','pending',NULL,'placed','','2026-04-20 09:50:21','2026-04-20 09:50:21',NULL,NULL,NULL),
(17,'DM35CF3080',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',399.00,99.00,0.00,498.00,'upi','pending',NULL,'placed','','2026-04-20 09:54:19','2026-04-20 09:54:19',NULL,NULL,NULL),
(18,'DME2430283',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',798.00,99.00,0.00,897.00,'upi','pending',NULL,'placed','','2026-04-20 09:56:06','2026-04-20 09:56:06',NULL,NULL,NULL),
(19,'DM0933CA18',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9234567890','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',8999.00,0.00,0.00,8999.00,'upi','pending',NULL,'delivered','','2026-04-20 10:04:40','2026-04-26 10:34:38',NULL,NULL,NULL),
(20,'DMF0073471',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','8617845142','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',4200.00,0.00,0.00,4200.00,'upi','pending',NULL,'confirmed','','2026-04-26 10:27:03','2026-04-26 10:33:14',NULL,NULL,NULL),
(21,'DME8957672',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',2199.00,0.00,0.00,2199.00,'upi','pending',NULL,'placed','','2026-05-01 07:10:30','2026-05-01 07:10:30',NULL,NULL,NULL),
(22,'DM7BDAEA68',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',2499.00,0.00,0.00,2499.00,'upi','pending',NULL,'placed','','2026-05-02 10:55:59','2026-05-02 10:55:59',NULL,NULL,NULL),
(23,'DM33C73C60',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',0.99,99.00,0.00,99.99,'upi','pending',NULL,'placed','','2026-05-02 10:59:07','2026-05-02 10:59:09','order_SkNeL2eT25JMbt',NULL,NULL),
(24,'DM63D36299',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',0.99,99.00,0.00,99.99,'upi','pending',NULL,'placed','','2026-05-02 11:00:30','2026-05-02 11:02:35','order_SkNhxrBrP6m5Rj',NULL,NULL),
(25,'DMB81AF775',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',0.99,99.00,0.00,99.99,'upi','pending',NULL,'placed','','2026-05-02 11:08:19','2026-05-02 11:08:21','order_SkNo3eaj4Tf96B',NULL,NULL),
(26,'DM83DE6359',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',0.99,0.00,0.00,0.99,'upi','pending',NULL,'placed','','2026-05-02 11:12:16','2026-05-02 11:12:16',NULL,NULL,NULL),
(27,'DM32D95A40',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',2.97,0.00,0.00,2.97,'upi','pending',NULL,'placed','','2026-05-02 11:12:43','2026-05-02 11:12:44','order_SkNshXAe7HtlIb',NULL,NULL),
(28,'DMCBB81F94',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-02 11:13:08','2026-05-02 11:13:10','order_SkNt9G8O4y4b3U',NULL,NULL),
(29,'DM89523456',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-03 05:48:32','2026-05-03 05:48:34','order_SkgtPZRVFMO01F',NULL,NULL),
(30,'DM9CEB4E74',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',0.99,0.00,0.00,0.99,'upi','pending',NULL,'placed','','2026-05-07 22:17:05','2026-05-07 22:17:05',NULL,NULL,NULL),
(31,'DM101C3814',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-07 22:17:29','2026-05-07 22:17:31','order_SmXscllfvnv3ga',NULL,NULL),
(32,'DM08ED0853',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-07 22:49:44','2026-05-07 22:49:46','order_SmYQh1LyQZfWXR',NULL,NULL),
(33,'DMDCE4A554',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-07 23:02:29','2026-05-07 23:02:31','order_SmYeAOjRieFJ0o',NULL,NULL),
(34,'DM30917494',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-08 07:44:27','2026-05-08 07:44:27',NULL,NULL,NULL),
(35,'DM8E6BC480',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-08 07:44:48','2026-05-08 07:44:48',NULL,NULL,NULL),
(36,'DM20745811',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-08 07:48:10','2026-05-08 07:48:12','order_SmhbTTgUD3uZQX',NULL,NULL),
(37,'DM233B8D52',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-08 08:04:10','2026-05-08 08:04:12','order_SmhsNLNF6u1c21',NULL,NULL),
(38,'DM3B54B365',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','pending',NULL,'placed','','2026-05-08 08:07:39','2026-05-15 10:11:55',NULL,NULL,NULL),
(39,'DMADE6C755',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','paid','pay_SmiO49QzhwQSRt','confirmed','','2026-05-08 08:15:46','2026-05-08 08:34:44','order_Smi4djO0z61AO4','pay_SmiO49QzhwQSRt','c995cdfe99c5d804d643397b26d8f31db0a60052e5f52b22394f532b5b7da33a'),
(40,'DMB9BA2529',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1.98,0.00,0.00,1.98,'upi','paid','pay_SmiYliyIjjlHaG','confirmed','','2026-05-08 08:37:07','2026-05-08 08:44:48','order_SmiRBUN4IucwsP','pay_SmiYliyIjjlHaG','708e0b8b0eb91553353a0df8fb94e628706f434ea508ac457fc5bac4fdbf010f'),
(41,'DM88853511',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1798.00,0.00,0.00,1798.00,'upi','pending',NULL,'placed','','2026-05-10 09:54:08','2026-05-10 09:54:13','order_SnWoqxuI1DyC7T',NULL,NULL),
(42,'DM3E053523',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',4200.00,0.00,0.00,4200.00,'upi','paid','pay_SnWsw3dyxDQG30','confirmed','','2026-05-10 09:54:35','2026-05-10 09:58:45','order_SnWpJ7tt6HBVaD','pay_SnWsw3dyxDQG30','e466dc6c53c44cd7ad686339f5f21effdc50b2dbbb444f80682ef699fd114fb6'),
(43,'DM583FBF89',4,'abc','abc@gmail.com','9897656748','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',3.96,0.00,0.00,3.96,'upi','pending',NULL,'placed','','2026-05-11 21:37:49','2026-05-11 21:37:49',NULL,NULL,NULL),
(44,'DM512FC999',4,'abc','abc@gmail.com','9897656748','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',399.00,0.00,0.00,399.00,'upi','pending',NULL,'placed','','2026-05-11 21:58:37','2026-05-11 21:58:39','order_So7hGVoWcYQvIZ',NULL,NULL),
(45,'DM10C50F54',4,'abc','abc@gmail.com','9897656748','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',899.00,0.00,0.00,899.00,'upi','pending',NULL,'placed','','2026-05-11 21:59:21','2026-05-11 21:59:23','order_So7i2pwUVR39C3',NULL,NULL),
(46,'DMBEC9C434',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',33600.00,0.00,0.00,33600.00,'upi','pending',NULL,'placed','','2026-05-13 22:20:35','2026-05-13 22:20:37','order_Sov8kvsue765wB',NULL,NULL),
(47,'DM98F6B916',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',2697.00,0.00,0.00,2697.00,'upi','paid','pay_SovfcbpLhp7ZbR','confirmed','','2026-05-13 22:51:13','2026-05-13 22:52:04','order_Sovf77WvY96VdH','pay_SovfcbpLhp7ZbR','688427ccd3b6991ec54d59b1c46572b8b7c6dd591f328634c6fcf4c6d33204c7'),
(48,'DM1DE2C661',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',899.00,0.00,0.00,899.00,'upi','paid','pay_SovhYODWtu1tHk','confirmed','','2026-05-13 22:52:57','2026-05-13 22:53:55','order_SovgxDsUTZTKH1','pay_SovhYODWtu1tHk','5b3193f0780b975e32f12b2409a729b37d5f07d5a92f610e16b6b949b3a2675c'),
(49,'DMC95F3D64',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',899.00,0.00,0.00,899.00,'upi','paid','pay_SovvI67S3d4DU7','confirmed','','2026-05-13 23:06:12','2026-05-13 23:07:00','order_SovuvqIrkwIxaQ','pay_SovvI67S3d4DU7','b2999703fd5e3647ffb95b0b08425d8ac913b9b4856f16c1bc6aba1a2f4a0563'),
(50,'DMA95B9280',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',899.00,0.00,0.00,899.00,'upi','paid','pay_Sp5IioCRr0pW9z','confirmed','','2026-05-14 08:15:46','2026-05-14 08:17:18','order_Sp5HUINrs5zsws','pay_Sp5IioCRr0pW9z','931a9b0d44b8e6782b42e6736e8982e02bb6006ca919773dddeb8bf8066ba052'),
(51,'DM7C893390',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',899.00,0.00,0.00,899.00,'upi','paid','pay_Sp5lIK08gR4c4D','confirmed','','2026-05-14 08:43:43','2026-05-14 08:44:23','order_Sp5kzdqD28ClEL','pay_Sp5lIK08gR4c4D','1a2bc2c1b91233479b6c1e8065168196b21ccc2db52ebdd4956bb259bced5984'),
(52,'DM0266EA35',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',899.00,0.00,0.00,899.00,'upi','paid','pay_Sp6MjAGEHiiv6x','confirmed','','2026-05-14 09:19:04','2026-05-14 09:19:47','order_Sp6MK2AUcvegF1','pay_Sp6MjAGEHiiv6x','3802bd65a72477cd66960036df01259e26bd2f395e9bab34d3d25289241216b6'),
(53,'DM9EC0E194',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',17980.00,0.00,0.00,17980.00,'upi','pending',NULL,'placed','','2026-05-14 09:22:41','2026-05-15 12:23:03',NULL,NULL,NULL),
(54,'DM51BBEB25',3,'shubhajit','shubhajitgayen65@gmail.com','9641541210','kalkata,west Bengal','kalkata','West Bengal','987678',4200.00,0.00,0.00,4200.00,'upi','paid','pay_SpVCeLf2ndjyXr','confirmed','','2026-05-15 09:36:29','2026-05-15 09:37:35','order_SpVBrhDYN6vNAZ','pay_SpVCeLf2ndjyXr','79ba68308a98ce2074894dd500c46c0406ba868ee97cc3dea3009584b8a6f414'),
(55,'DMB48B2317',3,'shubhajit','shubhajitgayen65@gmail.com','9641541210','kalkata,west Bengal','kalkata','West Bengal','987678',2199.00,0.00,0.00,2199.00,'upi','paid','pay_SpVc0YpPrDyUVt','confirmed','','2026-05-15 09:37:55','2026-05-15 10:01:36','order_SpVa8OLgBpViDC','pay_SpVc0YpPrDyUVt','7e5a203d1fa44f11460b6bd52cd70399b759b051d036ce0853b4e8218e920130'),
(56,'DMCBC2E514',3,'shubhajit','shubhajitgayen65@gmail.com','9641541210','kalkata,west Bengal','kalkata','West Bengal','987678',899.00,0.00,0.00,899.00,'upi','paid','pay_SpVFhpZJbLaq9b','confirmed','','2026-05-15 09:39:48','2026-05-15 09:40:27','order_SpVFNYQHGyZqIQ','pay_SpVFhpZJbLaq9b','4f745f0c5d8abf69198e28cf3da7d012373f4426170c84f2c65bf18f28bbbf61'),
(57,'DM215E8525',3,'shubhajit','shubhajitgayen65@gmail.com','9641541210','kalkata,west Bengal','kalkata','West Bengal','987678',399.00,0.00,0.00,399.00,'upi','paid','pay_SpYNx01FaFLIg7','confirmed','','2026-05-15 10:02:02','2026-05-15 12:44:23','order_SpYMwF89KqeZuA','pay_SpYNx01FaFLIg7','08ba743ff2b4f5d6aeafc4136201a8165e980109e6227e85a5ddbe2bd2d1db85'),
(58,'DMCDF3DA71',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',4200.00,0.00,0.00,4200.00,'upi','pending',NULL,'placed','','2026-05-17 14:31:16','2026-05-17 14:31:16',NULL,NULL,NULL),
(59,'DM3877A030',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',4200.00,0.00,0.00,4200.00,'upi','pending',NULL,'placed','','2026-05-17 14:31:39','2026-05-17 14:31:39',NULL,NULL,NULL),
(60,'DMD78E5797',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',1197.00,0.00,0.00,1197.00,'upi','pending',NULL,'placed','','2026-05-17 14:32:37','2026-05-17 14:32:37',NULL,NULL,NULL),
(61,'DME4848661',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',4200.00,0.00,0.00,4200.00,'upi','paid','pay_SqNLKbYTQ7FYsy','confirmed','','2026-05-17 14:34:14','2026-05-17 14:35:16','order_SqNKhWQOpBXJfY','pay_SqNLKbYTQ7FYsy','66640b85d2f83b129020e29171a729d0121fa935b4fc478ad2d1548d22a5055a'),
(62,'DM4B2FA845',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9876543210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',2199.00,0.00,0.00,2199.00,'upi','pending',NULL,'placed','','2026-05-17 14:49:32','2026-05-17 14:49:32',NULL,NULL,NULL),
(63,'DM31476A22',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9749724552','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',2199.00,0.00,0.00,2199.00,'upi','pending',NULL,'placed','','2026-05-17 14:50:51','2026-05-17 14:50:51',NULL,NULL,NULL),
(64,'DM314B9998',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',2199.00,0.00,0.00,2199.00,'upi','pending',NULL,'placed','','2026-05-17 14:52:43','2026-05-17 14:52:43',NULL,NULL,NULL),
(65,'DMB4315491',NULL,'Shubhajit Gayen','shubhajitgayen65@gmail.com','9641541210','VILLAGE-bILARA,P.O-HAWAKHANA,P.S-JANGIPARA,712403','SRIRAMPUR','WEST BENGAL','712403',899.00,0.00,0.00,899.00,'upi','pending',NULL,'placed','','2026-05-18 06:27:47','2026-05-18 06:27:47',NULL,NULL,NULL),
(66,'DM5780C735',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',1798.00,0.00,0.00,1798.00,'upi','pending',NULL,'placed','','2026-08-21 12:20:13','2026-08-21 12:20:16','order_TSKKZvv5z9szfe',NULL,NULL),
(67,'DM8865D397',2,'user','user@gmail.com','9749724552','bh','bhj','west bengal','987654',1798.00,0.00,0.00,1798.00,'upi','paid',NULL,'delivered','','2026-08-21 12:20:32','2026-09-01 09:55:18','order_TSKKuEx9F9SxAI',NULL,NULL);

/*Table structure for table `products` */

DROP TABLE IF EXISTS `products`;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(220) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `fabric` varchar(100) DEFAULT NULL,
  `color` varchar(100) DEFAULT NULL,
  `size` varchar(200) DEFAULT NULL,
  `occasion` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_products_stock` (`stock`),
  KEY `fk_products_category` (`category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_stock_non_negative` CHECK (`stock` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `products` */

insert  into `products`(`id`,`category_id`,`name`,`slug`,`description`,`short_description`,`price`,`sale_price`,`sku`,`stock`,`fabric`,`color`,`size`,`occasion`,`image`,`gallery`,`is_featured`,`is_active`,`created_at`,`updated_at`) values 
(1,2,'Kanjivaram Pure Silk Dhoti','kanjivaram-pure-silk-dhoti','Authentic Kanjivaram pure silk dhoti with zari border. Handwoven by master weavers from Tamil Nadu. Perfect for weddings, festivals and special occasions. The rich golden zari work gives it a regal appearance.','Authentic Kanjivaram pure silk with golden zari border',3499.00,2999.00,'',50,'Pure Silk','Cream with Gold Border','4 Meters, 4.5 Meters, 5 Meters','Wedding, Festival, Puja',NULL,NULL,1,1,'2026-03-27 09:45:38','2026-03-27 13:59:23'),
(2,2,'Banarasi Silk Dhoti','banarasi-silk-dhoti','Exquisite Banarasi silk dhoti featuring intricate brocade patterns. Made from the finest silk sourced from Varanasi. The brocade work is done by traditional weavers maintaining age-old techniques.','Luxurious Banarasi silk with brocade patterns',4200.00,3799.00,NULL,28,'Banarasi Silk','Off-White with Silver Border','4 Meters, 5 Meters','Wedding, Reception, Festival',NULL,NULL,1,1,'2026-03-27 09:45:38','2026-05-17 14:35:16'),
(3,1,'Bengal Handloom Dhoti','bengal-handloom-dhoti','Traditional Bengali dhoti handwoven on traditional looms. Features the classic white with red/blue border combination beloved in West Bengal. Ideal for Durga Puja and other Bengali festivals.','Classic Bengal handloom with traditional border',899.00,749.00,NULL,94,'Cotton Handloom','White with Red Border','4.5 Meters, 5 Meters','Festival, Daily, Puja',NULL,NULL,1,1,'2026-03-27 09:45:38','2026-05-15 09:40:27'),
(4,3,'Kerala Kasavu Dhoti','kerala-kasavu-dhoti','Authentic Kerala Kasavu dhoti with traditional golden kasavu border. Made from fine cotton grown in Kerala. An essential garment for Onam, Vishu and all Kerala festivals.','Traditional Kerala cotton with golden kasavu border',1299.00,1099.00,NULL,75,'Fine Cotton','White with Gold Border','4 Meters, 4.5 Meters','Onam, Festival, Puja',NULL,NULL,1,1,'2026-03-27 09:45:38','2026-03-27 09:45:38'),
(5,4,'Embroidered Designer Dhoti','embroidered-designer-dhoti','Contemporary designer dhoti with hand embroidery work. A modern take on traditional dhoti perfect for mehendi, engagement and sangeet ceremonies. Pairs beautifully with designer kurtas.','Modern dhoti with intricate hand embroidery',2199.00,1899.00,NULL,38,'Cotton Silk Blend','Ivory with Maroon Embroidery','4 Meters, 4.5 Meters','Engagement, Mehendi, Sangeet',NULL,NULL,1,1,'2026-03-27 09:45:38','2026-05-15 10:01:36'),
(6,5,'Royal Wedding Dhoti Kurta Set','royal-wedding-dhoti-kurta-set','Complete royal wedding set including premium silk dhoti and matching embroidered kurta. The set is adorned with delicate threadwork and comes with an angavastram. A complete traditional ensemble for the groom.','Complete bridal set with dhoti, kurta and angavastram',8999.00,7499.00,NULL,20,'Silk Blend','Cream and Gold','S, M, L, XL, XXL','Wedding, Engagement',NULL,NULL,1,1,'2026-03-27 09:45:38','2026-03-27 09:45:38'),
(7,3,'Soft Cotton Daily Dhoti','soft-cotton-daily-dhoti','Premium soft cotton dhoti ideal for daily wear. Lightweight, breathable and comfortable for all-day wear. Easy to drape and maintain. Available in multiple colors.','Comfortable everyday cotton dhoti',399.00,NULL,'',199,'Soft Cotton','White','4 Meters, 4.5 Meters, 5 Meters','Daily Wear, Home','prod_69c7e0afe3970.png',NULL,0,1,'2026-03-27 09:45:38','2026-05-15 12:44:23'),
(8,6,'Silk Angavastram','silk-angavastram','Pure silk angavastram to complement your dhoti. Adds grace and elegance to traditional attire. Available in multiple colors to match different dhotis.','Pure silk angavastram for traditional attire',999.00,849.00,'',60,'Pure Silk','Multiple Colors','2.5 Meters','Wedding, Festival, Puja','prod_69c7e0799199d.png',NULL,0,1,'2026-03-27 09:45:38','2026-04-09 07:33:40'),
(9,1,'Lucknowi Chikankari Dhoti','lucknowi-chikankari-dhoti','Elegant hand-embroidered Chikankari dhoti from the heart of Lucknow. Features intricate floral patterns hand-carved by skilled artisans on premium voile cotton. Breathable, stylish, and perfect for summer weddings or festive gatherings.','Hand-embroidered Lucknowi Chikankari on premium cotton.',400.00,450.00,'DM-CHIKAN-007',3,'Premium Voile Cotton','White with White Embroidery','4 Meters, 4.5 Meters','Summer Wedding, Eid, Sangeet','prod_69c6453ba0c8f.jpg',NULL,0,1,'2026-03-27 14:20:30','2026-08-13 10:43:01');

/*Table structure for table `settings` */

DROP TABLE IF EXISTS `settings`;

CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=222 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `settings` */

insert  into `settings`(`id`,`setting_key`,`setting_value`,`updated_at`) values 
(1,'site_name','Dhoti Mahal','2026-03-31 08:36:40'),
(2,'site_tagline','The House of Traditional Indian Attire','2026-03-27 09:45:38'),
(3,'site_email','info@dhotimahal.com','2026-03-27 09:45:38'),
(4,'site_phone','+91 9641541210','2026-03-31 09:04:31'),
(5,'site_address','[User Village]','2026-03-29 21:46:14'),
(6,'upi_id','gayenshubhajit7@oksbi','2026-05-01 08:10:17'),
(7,'upi_name','Dhoti Mahal','2026-03-27 09:45:38'),
(8,'free_shipping_above','0','2026-05-02 11:11:59'),
(9,'shipping_cost','0','2026-05-02 11:11:59'),
(10,'currency_symbol','₹','2026-03-27 09:45:38'),
(11,'facebook_url','https://facebook.com/dhotimahal','2026-03-27 09:45:38'),
(12,'instagram_url','https://instagram.com/dhotimahal','2026-03-27 09:45:38'),
(13,'whatsapp_number','919641541210','2026-03-31 09:09:16'),
(14,'gst_number','','2026-05-01 08:10:17'),
(15,'meta_description','Dhoti Mahal - Your destination for premium traditional Indian dhotis. Shop pure silk, cotton handloom and designer dhotis for weddings and festivals.','2026-03-27 09:45:38'),
(128,'razorpay_key_id','rzp_test_SkNZoBd82hTJHV','2026-05-02 10:55:19'),
(129,'razorpay_key_secret','WxSTu53YHvgJT6wX8D12v7l7','2026-05-02 10:55:19'),
(130,'razorpay_name','Dhoti Mahal','2026-05-01 07:28:21'),
(131,'razorpay_logo','http://localhost/dhoti-mahal/assets/images/logo.png','2026-05-01 07:28:21');

/*Table structure for table `users` */

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `pincode` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `users` */

insert  into `users`(`id`,`name`,`email`,`phone`,`password`,`address`,`city`,`state`,`pincode`,`is_active`,`created_at`,`updated_at`) values 
(2,'user','user@gmail.com','9749724552','$2y$12$CrPgNjHGDOBz0VDWVU16UuiICkdMCH8cA.Y4xVTYlVtbyrmeggU7S','bh','bhj','west bengal','987654',1,'2026-03-27 10:13:45','2026-03-31 16:14:57'),
(3,'shubhajit','shubhajitgayen65@gmail.com','9641541210','$2y$12$oaQI5Ihcp8VTHBKLwMaJA.erGWXdeiqvubzq.x39C8Sg/dzZpQ3Rq','kalkata,west Bengal','kalkata','West Bengal','987678',1,'2026-04-09 14:31:03','2026-05-15 09:36:06'),
(4,'abc','abc@gmail.com','9897656748','$2y$12$KUzqDiPerQpi/hzgPZ8FUOBKeJLZwk9NtZUFp5L46FnfS7wp.gsPa',NULL,NULL,NULL,NULL,1,'2026-05-11 21:37:22','2026-05-11 21:37:22');

/*Table structure for table `whatsapp_messages` */

DROP TABLE IF EXISTS `whatsapp_messages`;

CREATE TABLE `whatsapp_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `message` text NOT NULL,
  `template_key` varchar(100) DEFAULT NULL,
  `status` enum('draft','sent','failed') DEFAULT 'draft',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `status` (`status`),
  CONSTRAINT `whatsapp_messages_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `whatsapp_messages` */

insert  into `whatsapp_messages`(`id`,`order_id`,`phone`,`message`,`template_key`,`status`,`sent_at`,`created_at`,`updated_at`) values 
(1,5,'9749724552','hi','custom_message','sent','2026-03-31 09:53:20','2026-03-31 09:53:20','2026-03-31 09:53:20'),
(2,5,'9749724552','Hi user,\n\nThank you for your order! ????\n\nOrder #: DM77673E98\nTotal: ₹9,996.00\n\nWe are processing your order and will update you soon.\n\nTrack your order: http://localhost/dhoti-mahal/track-order.php?order=DM77673E98\n\nThank you for choosing Dhoti Mahal!\n\nFor any queries, reply to this message.','order_confirmation','sent','2026-03-31 09:53:33','2026-03-31 09:53:33','2026-03-31 09:53:33'),
(3,5,'9749724552','Great news user! ????\n\nYour order #DM77673E98 has been shipped!\n\nCarrier: {carrier_name}\nTracking ID: {tracking_id}\n\nExpected Delivery: {delivery_date}\n\nTrack your package: http://localhost/dhoti-mahal/track-order.php?order=DM77673E98','order_shipped','sent','2026-03-31 09:54:06','2026-03-31 09:54:06','2026-03-31 09:54:06'),
(4,6,'9749724552','Hi user,\n\nThank you for your order! ????\n\nOrder #: DM36C75E98\nTotal: ₹2,199.00\n\nWe are processing your order and will update you soon.\n\nTrack your order: http://localhost/dhoti-mahal/track-order.php?order=DM36C75E98\n\nThank you for choosing Dhoti Mahal!\n\nFor any queries, reply to this message.','order_confirmation','sent','2026-04-08 18:08:41','2026-04-08 18:08:41','2026-04-08 18:08:41'),
(5,6,'9749724552','Hi user,\n\nThank you for your order! ????\n\nOrder #: DM36C75E98\nTotal: ₹2,199.00\n\nWe are processing your order and will update you soon.\n\nTrack your order: http://localhost/dhoti-mahal/track-order.php?order=DM36C75E98\n\nThank you for choosing Dhoti Mahal!\n\nFor any queries, reply to this message.','order_confirmation','sent','2026-04-08 18:09:57','2026-04-08 18:09:57','2026-04-08 18:09:57'),
(6,6,'9749724552','Hi user,\n\nThank you for your order! ????\n\nOrder #: DM36C75E98\nTotal: ₹2,199.00\n\nWe are processing your order and will update you soon.\n\nTrack your order: http://localhost/dhoti-mahal/track-order.php?order=DM36C75E98\n\nThank you for choosing Dhoti Mahal!\n\nFor any queries, reply to this message.','order_confirmation','sent','2026-04-08 18:11:01','2026-04-08 18:11:01','2026-04-08 18:11:01'),
(7,6,'9749724552','Wonderful! ????\n\nYour order #DM36C75E98 has been delivered!\n\nThank you for shopping with Dhoti Mahal. We hope you love your purchase!\n\nFeel free to reach out for any feedback or issues.','order_delivered','sent','2026-04-09 07:30:04','2026-04-09 07:30:04','2026-04-09 07:30:04'),
(8,6,'9749724552','hi','custom_message','sent','2026-04-09 07:31:03','2026-04-09 07:31:03','2026-04-09 07:31:03'),
(9,67,'9749724552','Great news user! ????\n\nYour order #DM8865D397 has been shipped!\n\nCarrier: {carrier_name}\nTracking ID: {tracking_id}\n\nExpected Delivery: {delivery_date}\n\nTrack your package: http://localhost/dhoti-mahal/track-order.php?order=DM8865D397','order_shipped','sent','2026-09-01 09:53:54','2026-09-01 09:53:54','2026-09-01 09:53:54');

/*Table structure for table `whatsapp_templates` */

DROP TABLE IF EXISTS `whatsapp_templates`;

CREATE TABLE `whatsapp_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key_name` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `template` text NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `variables` text DEFAULT NULL COMMENT 'JSON array of variable names like [customer_name, order_number, order_status]',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_name` (`key_name`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*Data for the table `whatsapp_templates` */

insert  into `whatsapp_templates`(`id`,`key_name`,`title`,`template`,`description`,`variables`,`is_active`,`created_at`,`updated_at`) values 
(1,'order_confirmation','Order Confirmation','Hi {customer_name},\n\nThank you for your order! ????\n\nOrder #: {order_number}\nTotal: {order_total}\n\nWe are processing your order and will update you soon.\n\nTrack your order: {tracking_link}\n\nThank you for choosing Dhoti Mahal!\n\nFor any queries, reply to this message.','Sent when order is confirmed','[\"customer_name\", \"order_number\", \"order_total\", \"tracking_link\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13'),
(2,'order_processing','Order Processing','Hi {customer_name},\n\nYour order #{order_number} is now being processed.\n\nWe will ship it within 2-3 business days.\n\nTrack: {tracking_link}','Sent when order status changes to processing','[\"customer_name\", \"order_number\", \"tracking_link\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13'),
(3,'order_shipped','Order Shipped','Great news {customer_name}! ????\n\nYour order #{order_number} has been shipped!\n\nCarrier: {carrier_name}\nTracking ID: {tracking_id}\n\nExpected Delivery: {delivery_date}\n\nTrack your package: {tracking_link}','Sent when order is shipped','[\"customer_name\", \"order_number\", \"carrier_name\", \"tracking_id\", \"delivery_date\", \"tracking_link\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13'),
(4,'order_delivered','Order Delivered','Wonderful! ????\n\nYour order #{order_number} has been delivered!\n\nThank you for shopping with Dhoti Mahal. We hope you love your purchase!\n\nFeel free to reach out for any feedback or issues.','Sent when order is delivered','[\"order_number\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13'),
(5,'order_delayed','Order Delayed','Hi {customer_name},\n\nWe apologize! Your order #{order_number} is experiencing a slight delay.\n\nNew Delivery Date: {new_delivery_date}\n\nWe appreciate your patience and will ensure fastest delivery.\n\nTrack: {tracking_link}','Sent when delivery is delayed','[\"customer_name\", \"order_number\", \"new_delivery_date\", \"tracking_link\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13'),
(6,'payment_reminder','Payment Reminder','Hi {customer_name},\n\nThis is a reminder about your pending payment for order #{order_number}.\n\nAmount: {order_total}\n\nPlease complete the payment at your earliest convenience.\n\nLink: {payment_link}','Sent for pending payments','[\"customer_name\", \"order_number\", \"order_total\", \"payment_link\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13'),
(7,'custom_message','Custom Message','{message}','Send a custom message to customer','[\"message\"]',1,'2026-03-31 09:43:13','2026-03-31 09:43:13');

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
