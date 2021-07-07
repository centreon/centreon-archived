import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { always, cond, lt, lte, map, pick, T } from 'ramda';
import { ParentSize } from '@visx/visx';

import {
  Paper,
  makeStyles,
  ButtonGroup,
  Button,
  useTheme,
  Tooltip,
} from '@material-ui/core';

import {
  ChangeCustomTimePeriodProps,
  CustomTimePeriod,
  TimePeriodId,
  timePeriods,
} from '../../../Details/tabs/Graph/models';
import GraphOptions from '../ExportableGraphWithTimeline/GraphOptions';

import CustomTimePeriodPickers from './CustomTimePeriodPickers';

const useStyles = makeStyles((theme) => ({
  button: {
    fontSize: theme.typography.body2.fontSize,
  },
  buttonGroup: {
    alignSelf: 'center',
  },
  header: {
    alignItems: 'center',
    columnGap: `${theme.spacing(2)}px`,
    display: 'grid',
    gridTemplateColumns: `repeat(3, auto)`,
    justifyContent: 'center',
    padding: theme.spacing(1, 0.5),
  },
}));

interface Props {
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
  customTimePeriod: CustomTimePeriod;
  disabled?: boolean;
  onChange: (timePeriod: TimePeriodId) => void;
  selectedTimePeriodId?: string;
}

const timePeriodOptions = map(pick(['id', 'name', 'largeName']), timePeriods);

const TimePeriodButtonGroup = ({
  selectedTimePeriodId,
  onChange,
  disabled = false,
  customTimePeriod,
  changeCustomTimePeriod,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles();
  const theme = useTheme();

  const translatedTimePeriodOptions = timePeriodOptions.map((timePeriod) => ({
    ...timePeriod,
    largeName: t(timePeriod.largeName),
    name: t(timePeriod.name),
  }));

  const changeDate = ({ property, date }) =>
    changeCustomTimePeriod({ date, property });

  return (
    <ParentSize>
      {({ width }) => {
        const isCompact = lt(width, theme.breakpoints.values.sm);
        return (
          <Paper className={classes.header}>
            <ButtonGroup
              className={classes.buttonGroup}
              color="primary"
              component="span"
              disabled={disabled}
              size="small"
            >
              {map(
                ({ id, name, largeName }) => (
                  <Tooltip key={name} placement="top" title={largeName}>
                    <Button
                      className={classes.button}
                      component="span"
                      variant={
                        selectedTimePeriodId === id ? 'contained' : 'outlined'
                      }
                      onClick={() => onChange(id)}
                    >
                      {cond<number, string>([
                        [lte(theme.breakpoints.values.md), always(largeName)],
                        [T, always(name)],
                      ])(width)}
                    </Button>
                  </Tooltip>
                ),
                translatedTimePeriodOptions,
              )}
            </ButtonGroup>
            <CustomTimePeriodPickers
              acceptDate={changeDate}
              customTimePeriod={customTimePeriod}
              isCompact={isCompact}
            />
            <GraphOptions />
          </Paper>
        );
      }}
    </ParentSize>
  );
};

export default TimePeriodButtonGroup;
