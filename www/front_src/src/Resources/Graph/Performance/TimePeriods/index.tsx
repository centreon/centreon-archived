import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { always, cond, lt, lte, map, not, pick, T } from 'ramda';
import { Responsive } from '@visx/visx';

import {
  Paper,
  makeStyles,
  ButtonGroup,
  Button,
  useTheme,
  Tooltip,
  Theme,
} from '@material-ui/core';
import { CreateCSSProperties } from '@material-ui/styles';

import { useMemoComponent } from '@centreon/centreon-frontend/packages/centreon-ui/src';

import { timePeriods } from '../../../Details/tabs/Graph/models';
import GraphOptions from '../ExportableGraphWithTimeline/GraphOptions';
import { useResourceContext } from '../../../Context';

import CustomTimePeriodPickers from './CustomTimePeriodPickers';

interface StylesProps {
  disablePaper: boolean;
}

const useStyles = makeStyles<Theme, StylesProps>((theme) => ({
  button: {
    fontSize: theme.typography.body2.fontSize,
  },
  buttonGroup: {
    alignSelf: 'center',
  },
  header: ({ disablePaper }): CreateCSSProperties<StylesProps> => ({
    alignItems: 'center',
    backgroundColor: disablePaper ? 'transparent' : 'undefined',
    border: disablePaper ? 'unset' : 'undefined',
    boxShadow: disablePaper ? 'unset' : 'undefined',
    columnGap: `${theme.spacing(2)}px`,
    display: 'grid',
    gridTemplateColumns: `repeat(3, auto)`,
    justifyContent: 'center',
    padding: theme.spacing(1, 0.5),
  }),
}));

interface Props {
  disableGraphOptions?: boolean;
  disablePaper?: boolean;
  disabled?: boolean;
}

const timePeriodOptions = map(pick(['id', 'name', 'largeName']), timePeriods);

const TimePeriodButtonGroup = ({
  disabled = false,
  disableGraphOptions = false,
  disablePaper = false,
}: Props): JSX.Element => {
  const { t } = useTranslation();
  const classes = useStyles({ disablePaper });
  const theme = useTheme();

  const {
    customTimePeriod,
    changeCustomTimePeriod,
    changeSelectedTimePeriod,
    selectedTimePeriod,
  } = useResourceContext();

  const translatedTimePeriodOptions = timePeriodOptions.map((timePeriod) => ({
    ...timePeriod,
    largeName: t(timePeriod.largeName),
    name: t(timePeriod.name),
  }));

  const changeDate = ({ property, date }): void =>
    changeCustomTimePeriod({ date, property });

  return useMemoComponent({
    Component: (
      <Responsive.ParentSize>
        {({ width }): JSX.Element => {
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
                          selectedTimePeriod?.id === id
                            ? 'contained'
                            : 'outlined'
                        }
                        onClick={(): void => changeSelectedTimePeriod(id)}
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
              {not(disableGraphOptions) && <GraphOptions />}
            </Paper>
          );
        }}
      </Responsive.ParentSize>
    ),
    memoProps: [
      disabled,
      disableGraphOptions,
      disablePaper,
      selectedTimePeriod?.id,
    ],
  });
};

export default TimePeriodButtonGroup;
