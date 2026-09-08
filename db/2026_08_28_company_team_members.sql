-- Database Migration Script for Company Team Members (Sub-Users) Feature
-- Date: 2026-08-28

-- Add invitation token fields to the `user` table for password setup links
ALTER TABLE `user` 
ADD COLUMN `invite_token` VARCHAR(255) NULL AFTER `password_reset_token`,
ADD COLUMN `invite_token_created_at` INT(11) NULL AFTER `invite_token`;
