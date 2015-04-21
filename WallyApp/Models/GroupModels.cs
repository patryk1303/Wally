using System;
using System.Collections.Generic;
using System.Data.Entity;
using System.Linq;
using System.Web;
using System.ComponentModel.DataAnnotations;

namespace WallyApp.Models
{
    public class GroupModels
    {
        public int ID { get; set; }
        public string Name { get; set; }
        public int Owner { get; set; }
    }

    public class GroupCreateViewModel
    {
        [Required]
        [Display(Name="Name")]
        public string Name { get; set; }
    }
}