#!/bin/sh

OPTION=$1

if [ -f $2 ] ; 
then 
   EXCLUDEFILE=$2
else
   EXCLUDEFILE=/cygdrive$2
fi

if [ -d $3 ] ; 
then 
   SOURCE=$3
else
   SOURCE=/cygdrive$3
fi

if [ -d $4 ] ; 
then 
   TARGET=$4
else
   TARGET=/cygdrive$4
fi

#echo les variables
#echo $OPTION
#echo $EXCLUDEFILE
#echo $SOURCE
#echo $TARGET

rsync -$OPTION --delete --exclude-from=$EXCLUDEFILE $SOURCE $TARGET
