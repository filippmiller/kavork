#!/bin/bash
# Clear Yii2 Twig cache on deployment
echo "Clearing Twig cache..."
rm -rf site_demo/frontend/runtime/Twig/cache/*
rm -rf site_demo/backend/runtime/Twig/cache/* 2>/dev/null
echo "Twig cache cleared successfully"
