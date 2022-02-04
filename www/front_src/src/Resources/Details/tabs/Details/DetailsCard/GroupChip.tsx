import * as React from 'react';

import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';
import clsx from 'clsx';

import { Grid, Chip, Tooltip, Typography } from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';
import FilterListIcon from '@mui/icons-material/FilterList';
import makeStyles from '@mui/styles/makeStyles';
import IconButton from '@mui/material/IconButton';

import { labelConfigure, labelFilter } from '../../../../translatedLabels';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { Group } from '../../../models';

const useStyles = makeStyles((theme) => ({
  chip: {
    alignSelf: 'center',
    display: 'flex',
  },
  chipAction: {
    gridArea: '1/1',
    maxWidth: theme.spacing(14),
    minWidth: theme.spacing(8),
    overflow: 'hidden',
  },
  chipHover: {
    backgroundColor: theme.palette.primary.main,
    display: 'flex',
    gap: theme.spacing(0.25),
    gridArea: '1/1',
  },
  chipIconColor: {
    color: theme.palette.common.white,
  },
  chipLabel: {
    display: 'grid',
    justifyItems: 'center',
    minWidth: theme.spacing(7),
    overflow: 'hidden',
  },
  chipLabelColor: { color: 'transparent' },
}));

interface Props {
  group: Group;
  type: string;
}

const GroupChip = ({ group, type }: Props): JSX.Element => {
  const classes = useStyles();
  const { t } = useTranslation();

  const [isHovered, setIsHovered] = React.useState<boolean>(false);

  const setCriteriaAndNewFilter = useUpdateAtom(
    setCriteriaAndNewFilterDerivedAtom,
  );

  const filterByGroup = (): void => {
    setCriteriaAndNewFilter({
      name: type,
      value: [group],
    });
  };
  const mouseEnter = (): void => {
    setIsHovered(true);
  };

  const mouseLeave = (): void => {
    setIsHovered(false);
  };

  const configureGroup = (): void => {
    window.location.href = group.configuration_uri as string;
  };

  return (
    <Grid item className={classes.chip} key={group.id}>
      <Chip
        aria-label={`${group.name} Chip`}
        color="primary"
        label={
          <div className={classes.chipLabel}>
            <Tooltip title={group.name}>
              <Typography
                className={clsx(
                  classes.chipAction,
                  isHovered ? classes.chipLabelColor : '',
                )}
                variant="body2"
              >
                {group.name}
              </Typography>
            </Tooltip>
            {isHovered && (
              <Grid className={classes.chipHover}>
                <IconButton
                  aria-label={`${group.name} Filter`}
                  className={classes.chipIconColor}
                  size="small"
                  title={t(labelFilter)}
                  onClick={filterByGroup}
                >
                  <FilterListIcon fontSize="small" />
                </IconButton>
                <IconButton
                  aria-label={`${group.name} Configure`}
                  className={classes.chipIconColor}
                  size="small"
                  title={t(labelConfigure)}
                  onClick={configureGroup}
                >
                  <SettingsIcon fontSize="small" />
                </IconButton>
              </Grid>
            )}
          </div>
        }
        onMouseEnter={mouseEnter}
        onMouseLeave={mouseLeave}
      />
    </Grid>
  );
};

export default GroupChip;
