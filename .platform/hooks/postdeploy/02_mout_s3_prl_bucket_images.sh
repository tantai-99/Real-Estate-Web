#!/bin/bash

S3_BUCKET_MOUNT_DIR=/var/app/current/public/images
S3_BUCKET=s3-nhp-rf-stg-prl-public-bucket/images

echo "Mounting S3 Bucket ${S3_BUCKET} to directory ${S3_BUCKET_MOUNT_DIR} ..."

echo 'Checking if S3 Bucket mount directory exists...'
if [ ! -d ${S3_BUCKET_MOUNT_DIR} ]; then
	echo "Creating directory ${S3_BUCKET_MOUNT_DIR} ..."
	sudo mkdir -p ${S3_BUCKET_MOUNT_DIR}
	sudo chown webapp:webapp -R ${S3_BUCKET_MOUNT_DIR}
	uid=$(id -u webapp)
	gid=$(id -g webapp)
	if [ $? -ne 0 ]; then
		echo 'ERROR: Directory creation failed!'
		exit 1
	fi
else
	echo "Directory ${S3_BUCKET_MOUNT_DIR} already exists!"
fi

locale
whoami
export LANG=en_US.UTF-8
export LC_ALL=en_US.UTF-8
echo "After Export"
locale

sudo -u webapp aws s3 sync s3://${S3_BUCKET} ${S3_BUCKET_MOUNT_DIR} --quiet
if [ $? -ne 0 ]; then
	echo "mount -t efs -o tls ${S3_BUCKET}:/ ${S3_BUCKET_MOUNT_DIR}"
	sudo aws s3 sync s3://${S3_BUCKET} ${S3_BUCKET_MOUNT_DIR} --delete
	if [ $? -ne 0 ] ; then
		echo 'ERROR: Mount command failed!'
		exit 1
	fi
else
	echo "Directory ${S3_BUCKET_MOUNT_DIR} is already a valid mountpoint!"
fi

echo 'S3 Bucket mount complete.'