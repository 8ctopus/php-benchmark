# PHP benchmark

PHP benchmark script iterations based on the original work from Alessandro Torrisi [www.php-benchmark-script.com](http://www.php-benchmark-script.com)

# how to use

```bash
$ php benchmark.php
PHP benchmark

php version        :     7.4.5
platform           : WINNT x64
memory limit       :      128M
max execution      :         0
time per iteration :         1
iterations         :        10
------------------------------
test_math          :     13664 ±14.7% [11365 - 15395] - 13252 11365 12800 11445 13695 15395 14208 15291 13633 15286
test_strings       :     27501 ±2.2% [27342 - 28556] - 28556 27369 28170 27451 27342 28099 28154 27448 27409 27551
test_loops         :     36738 ±8.6% [31909 - 38248] - 38241 31909 38248 36096 36819 38090 36063 35986 36657 38199
test_if_else       :    955312 ±8.9% [838602 - 1008017] - 989053 970192 993150 959316 838602 1008017 946853 918702 951307 934248
test_arrays        :     25545 ±5.7% [24742 - 27664] - 25675 25541 25548 25006 25485 25675 26435 25239 27664 24742
test_hashes        :     23285 ±7.3% [20793 - 24181] - 23926 20793 24181 22616 23472 23748 22028 22590 23098 23484
test_files         :        84 ±8.9% [73 - 88] - 84 87 80 83 88 84 78 87 86 73
test_mysql         :        60 ±10.8% [50 - 63] - 55 54 62 60 60 50 60 63 53 62
```

# interesting reads
https://kinsta.com/blog/php-benchmarks/
