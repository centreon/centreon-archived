import { getCommandWithArguments } from '.';

describe(getCommandWithArguments, () => {
  it('parses the command and the associated arguments', () => {
    expect(
      getCommandWithArguments(
        "/usr/lib/centreon/plugins/centreon_ruckus_scg_snmp.pl --plugin=network::ruckus::scg::snmp::plugin --mode=memory --hostname=snmpsim.centreon.training --snmp-version='2c' --snmp-community='ruckus_scg' --warning-usage='80' --critical-usage='90' -d -p 50",
      ),
    ).toEqual({
      arguments: [
        ['--plugin=network::ruckus::scg::snmp::plugin'],
        ['--mode=memory'],
        ['--hostname=snmpsim.centreon.training'],
        ["--snmp-version='2c'"],
        ["--snmp-community='ruckus_scg'"],
        ["--warning-usage='80'"],
        ["--critical-usage='90'"],
        ['-d'],
        ['-p', '50'],
      ],
      command: '/usr/lib/centreon/plugins/centreon_ruckus_scg_snmp.pl',
    });
  });
});
