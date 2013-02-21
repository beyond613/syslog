#!/bin/bash
for ((i = 1; i <= 10; i++));do
{
	./sub_run.sh
} &
done
