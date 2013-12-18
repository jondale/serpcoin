#!/bin/sh

shred -n 10 cache/*
sync
shred -n 10 -z -u cache/*

