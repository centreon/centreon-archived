import * as React from 'react';

import { useTranslation } from 'react-i18next';
import { FormikValues, useFormikContext } from 'formik';
import clsx from 'clsx';

import {
  Button,
  ButtonProps,
  Stack,
  Typography,
  useTheme,
} from '@mui/material';
import makeStyles from '@mui/styles/makeStyles';

import { useMemoComponent } from '@centreon/ui';

import {
  labelForceToUseLowerCase,
  labelForceToUseNumbers,
  labelForceToUseSpecialCharacters,
  labelForceToUseUpperCase,
  labelGood,
  labelLowerCase,
  labelNumber,
  labelPasswordCases,
  labelSpecialCharacters,
  labelStrong,
  labelUpperCase,
  labelWeak,
} from '../../../translatedLabels';
import StrengthProgress from '../../../StrengthProgress';
import { getFields } from '../../utils';

import LabelWithTooltip from './LabelWithTooltip';

const activeButtonProps = {
  variant: 'contained',
} as ButtonProps;
const hasLowerCaseName = 'hasLowerCase';
const hasUpperCaseName = 'hasUpperCase';
const hasNumberName = 'hasNumber';
const hasSpecialCharacterName = 'hasSpecialCharacter';

const useStyles = makeStyles((theme) => ({
  button: {
    minWidth: theme.spacing(4),
  },
  caseButtonsContainer: {
    display: 'flex',
    flexDirection: 'column',
    rowGap: theme.spacing(0.5),
    width: 'fit-content',
  },
  lowerCaseButton: {
    textTransform: 'none',
  },
}));

const CaseButtons = (): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();
  const theme = useTheme();

  const { values, setFieldValue } = useFormikContext<FormikValues>();

  const selectCase = (caseName: string) => (): void => {
    setFieldValue(caseName, !values[caseName]);
  };

  const [hasLowerCase, hasUpperCase, hasNumber, hasSpecialCharacter] =
    getFields<boolean>({
      fields: [
        hasLowerCaseName,
        hasUpperCaseName,
        hasNumberName,
        hasSpecialCharacterName,
      ],
      object: values,
    });

  const thresholds = React.useMemo(
    () => [
      { color: theme.palette.error.main, label: labelWeak, value: 2 },
      { color: theme.palette.warning.main, label: labelGood, value: 3 },
      { color: theme.palette.success.main, label: labelStrong, value: 4 },
    ],
    [],
  );

  const thresholdValue = [
    hasLowerCase,
    hasUpperCase,
    hasNumber,
    hasSpecialCharacter,
  ].filter(Boolean).length;

  return useMemoComponent({
    Component: (
      <div className={classes.caseButtonsContainer}>
        <Typography variant="caption">Choose letter cases</Typography>
        <Stack aria-label={t(labelPasswordCases)} direction="row" spacing={1}>
          <Button
            aria-label={t(labelForceToUseLowerCase)}
            className={clsx(classes.lowerCaseButton, classes.button)}
            color="primary"
            size="small"
            variant="outlined"
            onClick={selectCase(hasLowerCaseName)}
            {...(hasLowerCase && activeButtonProps)}
          >
            <LabelWithTooltip
              label={labelLowerCase}
              tooltipLabel={labelForceToUseLowerCase}
            />
          </Button>
          <Button
            aria-label={t(labelForceToUseUpperCase)}
            className={classes.button}
            color="primary"
            size="small"
            variant="outlined"
            onClick={selectCase(hasUpperCaseName)}
            {...(hasUpperCase && activeButtonProps)}
          >
            <LabelWithTooltip
              label={labelUpperCase}
              tooltipLabel={labelForceToUseUpperCase}
            />
          </Button>
          <Button
            aria-label={t(labelForceToUseNumbers)}
            className={classes.button}
            color="primary"
            size="small"
            variant="outlined"
            onClick={selectCase(hasNumberName)}
            {...(hasNumber && activeButtonProps)}
          >
            <LabelWithTooltip
              label={labelNumber}
              tooltipLabel={labelForceToUseNumbers}
            />
          </Button>
          <Button
            aria-label={t(labelForceToUseSpecialCharacters)}
            className={classes.button}
            color="primary"
            size="small"
            variant="outlined"
            onClick={selectCase(hasSpecialCharacterName)}
            {...(hasSpecialCharacter && activeButtonProps)}
          >
            <LabelWithTooltip
              label={labelSpecialCharacters}
              tooltipLabel={labelForceToUseSpecialCharacters}
            />
          </Button>
        </Stack>
        <StrengthProgress
          max={4}
          thresholds={thresholds}
          value={thresholdValue}
        />
      </div>
    ),
    memoProps: [hasLowerCase, hasUpperCase, hasNumber, hasSpecialCharacter],
  });
};

export default CaseButtons;
