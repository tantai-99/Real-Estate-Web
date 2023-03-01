#!/bin/bash

S3_BUCKET_MOUNT_DIR=/var/app/current/public/cms_designselectpage
S3_BUCKET=s3-nhp-rf-stg-app-public-bucket/cms_designselectpage

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

aws s3 sync s3://${S3_BUCKET} ${S3_BUCKET_MOUNT_DIR} --quiet
if [ $? -ne 0 ]; then
	echo "mount -t efs -o tls ${S3_BUCKET}:/ ${S3_BUCKET_MOUNT_DIR}"
	aws s3 sync s3://${S3_BUCKET} ${S3_BUCKET_MOUNT_DIR} --delete
	if [ $? -ne 0 ] ; then
		echo 'ERROR: Mount command failed!'
		exit 1
	fi
else
	echo "Directory ${S3_BUCKET_MOUNT_DIR} is already a valid mountpoint!"
fi

echo 'S3 Bucket mount complete.'