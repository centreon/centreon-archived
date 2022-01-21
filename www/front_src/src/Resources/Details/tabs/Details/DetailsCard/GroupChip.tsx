import * as React from 'react';

import { useUpdateAtom } from 'jotai/utils';
import { useTranslation } from 'react-i18next';

import { Grid, Chip, Tooltip, Typography, useTheme } from '@mui/material';
import SettingsIcon from '@mui/icons-material/Settings';
import FilterListIcon from '@mui/icons-material/FilterList';
import makeStyles from '@mui/styles/makeStyles';
import IconButton from '@mui/material/IconButton';

import { labelConfigure, labelFilter } from '../../../../translatedLabels';
import { setCriteriaAndNewFilterDerivedAtom } from '../../../../Filter/filterAtoms';
import { Group } from '../../../models';

const useStyles = makeStyles((theme) => ({
  groupChip: {
    alignSelf: 'center',
    display: 'flex',
  },
  groupChipAction: {
    gridArea: '1/1',
    maxWidth: theme.spacing(14),
    overflow: 'hidden',
    textOverflow: 'ellipsis',
  },
  groupChipLabel: {
    display: 'grid',
    justifyItems: 'center',
    minWidth: theme.spacing(7),
    overflow: 'hidden',
  },
  groups: {
    display: 'flex',
    padding: theme.spacing(1),
  },
  iconAction: {
    backgroundColor: theme.palette.primary.main,
    display: 'flex',
    gap: theme.spacing(0.25),
    gridArea: '1/1',
  },
}));

interface GroupsChipProps {
  group: Group;
  type: string;
}

const GroupChip = ({ group, type }: GroupsChipProps): JSX.Element => {
  const theme = useTheme();
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

  return (
    <Grid item className={classes.groupChip} key={group.id}>
      <Chip
        color="primary"
        label={
          <div className={classes.groupChipLabel}>
            <Tooltip title={group.name}>
              <Typography
                className={classes.groupChipAction}
                style={{ color: isHovered ? 'transparent' : 'unset' }}
                variant="body2"
              >
                {group.name}
              </Typography>
            </Tooltip>
            {isHovered === true && (
              <Grid className={classes.iconAction}>
                <IconButton
                  style={{ color: theme.palette.common.white }}
                  title={t(labelFilter)}
                  onClick={(): void => filterByGroup()}
                >
                  <FilterListIcon fontSize="small" />
                </IconButton>
                <IconButton
                  style={{ color: theme.palette.common.white }}
                  title={t(labelConfigure)}
                  onClick={(): void => {
                    window.location.href = group.configuration_uri as string;
                  }}
                >
                  <SettingsIcon fontSize="small" />
                </IconButton>
              </Grid>
            )}
          </div>
        }
        onMouseEnter={(): void => setIsHovered(true)}
        onMouseLeave={(): void => setIsHovered(false)}
      />
    </Grid>
  );
};

export default GroupChip;
