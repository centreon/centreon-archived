import React from 'react';

import { Box, Typography, makeStyles, Button, Paper } from '@material-ui/core';
import {
  Settings as IconSettings,
  HelpOutline as IconHelp,
} from '@material-ui/icons';

import {
  TABLE_COLUMN_TYPES,
  Listing,
  SelectField,
  SearchField,
} from '@centreon/ui';
import { labelResourceName, labelSearch } from './translatedLabels';

const useStyles = makeStyles((theme) => ({
  page: {
    backgroundColor: theme.palette.background.default,
  },
  iconSettings: {
    color: theme.palette.primary.main,
  },
  filter: {
    padding: theme.spacing(1),
    backgroundColor: theme.palette.common.white,
  },
  listing: {
    marginLeft: theme.spacing(1),
    marginRight: theme.spacing(1),
  },
}));

interface FilterBoxProps {
  children: React.ReactNode;
}

const FilterBox = ({ children }: FilterBoxProps): JSX.Element => (
  <Box m={1}>{children}</Box>
);

const noOp = (): void => undefined;

const configuration = [
  { id: 'status', label: 'Status', type: TABLE_COLUMN_TYPES.string },
];

const Resources = (): JSX.Element => {
  const classes = useStyles();

  return (
    <div className={classes.page}>
      <Paper elevation={1} className={classes.filter}>
        <Box display="flex" alignItems="center">
          <FilterBox>
            <Typography variant="h6">Filter</Typography>
          </FilterBox>
          <FilterBox>
            <IconSettings className={classes.iconSettings} />
          </FilterBox>
          <FilterBox>
            <SelectField
              options={[{ id: 0, name: 'Centreon default' }]}
              selectedOptionId={0}
            />
          </FilterBox>
          <FilterBox>
            <SearchField
              EndAdornment={(): JSX.Element => <IconHelp />}
              placeholder={labelResourceName}
            />
          </FilterBox>
          <FilterBox>
            <Button variant="contained" color="primary" disabled>
              {labelSearch}
            </Button>
          </FilterBox>
        </Box>
      </Paper>
      <div className={classes.listing}>
        <Listing
          columnConfiguration={configuration}
          currentPage={0}
          limit={10}
          onDelete={noOp}
          onSort={noOp}
          onDuplicate={noOp}
          onPaginationLimitChanged={noOp}
          tableRows={0}
          tableData={[]}
          selectedRows={[]}
          checkable
        />
      </div>
    </div>
  );
};

export default Resources;
