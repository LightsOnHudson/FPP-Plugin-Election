#!/bin/bash
pushd $(dirname $(which $0))
target_PWD=$(readlink -f .)
exec /opt/fpp/scripts/update_plugin ${target_PWD##*/}
cp RUN-ELECTION.sh /home/fpp/media/scripts
popd
