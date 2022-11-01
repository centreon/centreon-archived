import { useState } from 'react';

import { useAtomValue } from 'jotai/utils';
import { makeStyles } from 'tss-react/mui';

import AddIcon from '@mui/icons-material/Add';
import {
  Button,
  Divider,
  List,
  ListItem,
  ListItemText,
  Typography,
} from '@mui/material';

import { selectedResourcesDetailsAtom } from '../../../../Details/detailsAtoms';
import PopoverCustomTimePeriodPickers from '../../TimePeriods/PopoverCustomTimePeriodPickers';
import { customTimePeriodAtom } from '../../TimePeriods/timePeriodAtoms';

import AnomalyDetectionCommentExclusionPeriod from './AnomalyDetectionCommentExclusionPeriods';
import AnomalyDetectionTitleExclusionPeriods from './AnomalyDetectionTitleExclusionPeriods';
import AnomalyDetectionFooterExclusionPeriods from './AnomalyDetectionFooterExclusionPeriods';

const useStyles = makeStyles()((theme) => ({
  body: {
    display: 'flex',
    justifyContent: 'center',
    marginTop: theme.spacing(5),
  },
  container: {
    display: 'flex',
    padding: theme.spacing(2),
  },
  divider: {
    margin: theme.spacing(0, 2),
  },
  excludedPeriods: {
    display: 'flex',
    flexDirection: 'column',
    width: '50%',
  },

  exclusionButton: {
    width: theme.spacing(22.5),
  },
  list: {
    backgroundColor: theme.palette.action.disabledBackground,
    maxHeight: theme.spacing(150 / 8),
    minHeight: theme.spacing(150 / 8),
    overflow: 'auto',
  },
  paper: {
    '& .MuiPopover-paper': {
      padding: theme.spacing(2),
      // width: '40%',
    },
  },
  picker: {
    flexDirection: 'row',
    padding: 0,
  },
  subContainer: {
    display: 'flex',
    flexDirection: 'column',
  },
  title: {
    color: theme.palette.text.disabled,
  },
}));

const AnomalyDetectionExclusionPeriod = (): JSX.Element => {
  const { classes } = useStyles();

  const [open, setOpen] = useState(false);

  const customTimePeriod = useAtomValue(customTimePeriodAtom);
  const selectedResource = useAtomValue(selectedResourcesDetailsAtom);
  console.log({ selectedResource });

  const exclude = (): void => {
    setOpen(true);
  };

  const anchorPosition = {
    left: window.innerWidth / 2,
    top: window.innerHeight / 4,
  };

  const close = (): void => {
    setOpen(false);
  };

  const changeDate = ({ property, date }): void => {
    console.log({ date, property });
  };

  return (
    <div className={classes.container}>
      <div className={classes.subContainer}>
        <Typography variant="h6">Exclusion of periods</Typography>
        <Typography variant="caption">
          Attention, the excluded of periods will be applied immediately.
        </Typography>
        <div className={classes.body}>
          <Button
            className={classes.exclusionButton}
            data-testid="exclude"
            size="small"
            startIcon={<AddIcon />}
            variant="contained"
            onClick={exclude}
          >
            Exclude a period
          </Button>
        </div>
      </div>
      <Divider flexItem className={classes.divider} orientation="vertical" />
      <div className={classes.excludedPeriods}>
        <Typography className={classes.title} variant="h6">
          Excluded periods
        </Typography>
        <List className={classes.list}>
          <ListItem>
            <ListItemText primary="test" />
          </ListItem>
        </List>
      </div>
      <PopoverCustomTimePeriodPickers
        acceptDate={changeDate}
        anchorReference="anchorPosition"
        classNamePaper={classes.paper}
        classNamePicker={classes.picker}
        customTimePeriod={customTimePeriod}
        open={open}
        reference={{ anchorPosition }}
        renderBody={<AnomalyDetectionCommentExclusionPeriod />}
        renderFooter={<AnomalyDetectionFooterExclusionPeriods />}
        renderTitle={<AnomalyDetectionTitleExclusionPeriods />}
        onClose={close}
      />
    </div>
  );
};

export default AnomalyDetectionExclusionPeriod;
