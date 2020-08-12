import React from 'react';

import { Paper } from '@material-ui/core';
import SettingsIcon from '@material-ui/icons/Settings';
import LogsIcon from '@material-ui/icons/Assignment';
import ReportIcon from '@material-ui/icons/Assessment';

import { ResourceLinks } from '../../../models';

interface Props {
  links: ResourceLinks;
}

const ShortcutsTab = ({ links }: Props): JSX.Element => {
  return (
    <Paper>
      <div>
        <SettingsIcon />
      </div>
    </Paper>
  );
};

export default ShortcutsTab;
