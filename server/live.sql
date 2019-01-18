/* 创建数据库 */
CREATE DATABASE `swooleapp` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

/**
 * 球队表
 */
 create table `live_team`(
     `id` smallint(4) UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT '主键',
     `name` CHAR(30) NOT NULL DEFAULT '' COMMENT '球队名称',
     `type` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '球队区域(1-东部,2-西部)',
     `logo` VARCHAR(250) NOT NULL DEFAULT '' COMMENT '球队logo',
     `player_quantity` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 1 COMMENT '球员人数',
     `create_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录生成时间',
     `update_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录更新时间',
     PRIMARY KEY(`id`)
 )ENGINE = InnoDB AUTO_INCREMENT = 1 CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

 /**
 * 球员表
 */
 create table `live_player`(
     `id` smallint(4) UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT '主键',
     `name` CHAR(30) NOT NULL DEFAULT '' COMMENT '球员名称',
     `team_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '所属球队ID',
     `age` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '年龄',
     `avatar` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '球员头像',
     `position` TINYINT(2) NOT NULL DEFAULT 0 COMMENT '球员位置(1-小前锋,2-大前锋,3-中锋,4-控球后卫,5-得分后卫)',
     `status` TINYINT(2) NOT NULL DEFAULT 1 COMMENT '球员状态(1-上场,2-未上场)',
     `create_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录生成时间',
     `update_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录更新时间',
     PRIMARY KEY(`id`)
 )ENGINE = InnoDB AUTO_INCREMENT = 1 CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

 /**
  * 比赛表
  */
 create table `live_game`(
     `id` smallint(4) UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT '主键',
     `home_field` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主场球队ID',
     `visiting_field` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '客场球队ID',
     `home_score` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '主场球队得分',
     `visiting_score` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '客场球队得分',
     `image` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '比赛主图片',
     `narrator` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '评论员',
     `start_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '比赛开始时间',
     `end_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '比赛结束时间',
     `status` TINYINT(2) NOT NULL DEFAULT 1 COMMENT '审核状态(1-待审核,2-审核通过,3-审核失败)',
     `create_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录生成时间',
     `update_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录更新时间',
     PRIMARY KEY(`id`)
 )ENGINE = InnoDB AUTO_INCREMENT = 1 CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

  /**
  * 比赛赛况表
  */
 create table `live_outs`(
     `id` smallint(4) UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT '主键',
     `game_id` SMALLINT(4) UNSIGNED  NOT NULL COMMENT '比赛ID',
     `team_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '赛况相关球队ID',
     `team_score` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '球队得分',
     `content` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '赛况内容',
     `image` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '比赛图片',
     `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '比赛节数',
     `status` TINYINT(2) NOT NULL DEFAULT 1 COMMENT '比赛(1-未开始,2-进行中,3-已结束)',
     `create_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录生成时间',
     `update_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录更新时间',
     PRIMARY KEY(`id`)
 )ENGINE = InnoDB AUTO_INCREMENT = 1 CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;

   /**
  * 比赛用户聊天表
  */
 create table `live_chart`(
     `id` smallint(4) UNSIGNED  NOT NULL AUTO_INCREMENT COMMENT '主键',
     `game_id` SMALLINT(4) UNSIGNED  NOT NULL COMMENT '比赛ID',
     `user_id` SMALLINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT '用户ID',
     `content` VARCHAR(200) NOT NULL DEFAULT '' COMMENT '聊天内容',
     `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '比赛节数',
     `create_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录生成时间',
     `update_time` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT '记录更新时间',
     PRIMARY KEY(`id`)
 )ENGINE = InnoDB AUTO_INCREMENT = 1 CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci;