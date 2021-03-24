import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { always, cond, lte, map, pick, T } from 'ramda';
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

import CustomTimePeriodPickers from './CustomTimePeriodPickers';

const useStyles = makeStyles((theme) => ({
  header: {
    padding: theme.spacing(1, 0.5),
    display: 'grid',
    gridTemplateColumns: `repeat(2, auto)`,
    columnGap: `${theme.spacing(2)}px`,
    justifyContent: 'center',
  },
  buttonGroup: {
    alignSelf: 'center',
  },
  button: {
    fontSize: theme.typography.body2.fontSize,
  },
}));

interface Props {
  selectedTimePeriodId?: string;
  onChange: (timePeriod: TimePeriodId) => void;
  disabled?: boolean;
  customTimePeriod: CustomTimePeriod;
  changeCustomTimePeriod: (props: ChangeCustomTimePeriodProps) => void;
}

const timePeriodOptions = map(
  pick(['id', 'name', 'compactName', 'largeName']),
  timePeriods,
);

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
    name: t(timePeriod.name),
    compactName: t(timePeriod.compactName),
    largeName: t(timePeriod.largeName),
  }));

  const changeDate = ({ property, date }) =>
    changeCustomTimePeriod({ date, property });

  return (
    <ParentSize>
      {({ width }) => {
        return (
          <Paper className={classes.header}>
            <ButtonGroup
              size="small"
              disabled={disabled}
              color="primary"
              className={classes.buttonGroup}
              component="span"
            >
              {map(
                ({ id, name, compactName, largeName }) => (
                  <Tooltip key={name} title={largeName} placement="top">
                    <Button
                      onClick={() => onChange(id)}
                      variant={
                        selectedTimePeriodId === id ? 'contained' : 'outlined'
                      }
                      className={classes.button}
                      component="span"
                    >
                      {cond<number, string>([
                        [lte(theme.breakpoints.values.md), always(largeName)],
                        [lte(theme.breakpoints.values.sm), always(name)],
                        [T, always(compactName)],
                      ])(width)}
                    </Button>
                  </Tooltip>
                ),
                translatedTimePeriodOptions,
              )}
            </ButtonGroup>
            <CustomTimePeriodPickers
              customTimePeriod={customTimePeriod}
              acceptDate={changeDate}
            />
          </Paper>
        );
      }}
    </ParentSize>
  );
};

export default TimePeriodButtonGroup;
