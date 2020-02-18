import React from 'react';

import { Box, Typography, makeStyles } from '@material-ui/core';
import { Settings as IconSettings } from '@material-ui/icons';

import { SelectField } from '@centreon/ui';

const useStyles = makeStyles((theme) => ({
  iconSettings: {
    color: theme.palette.primary.main,
  },
}));

const Resources = (): JSX.Element => {
  const classes = useStyles();

  return (
    <Box display="flex" m={2}>
      <Box m={1}>
        <Typography variant="h6">Filter</Typography>
      </Box>
      <Box m={1}>
        <IconSettings className={classes.iconSettings} />
      </Box>
      <Box m={0.5}>
        <SelectField
          options={[{ id: 0, name: 'Centreon default' }]}
          selectedOptionId={0}
        />
      </Box>
    </Box>
  );
};

export default Resources;
